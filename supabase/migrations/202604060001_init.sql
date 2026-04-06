-- Extensões
create extension if not exists pgcrypto;

-- Enums
create type public.membership_role as enum ('superadmin','owner','manager','agent','viewer');
create type public.tenant_status as enum ('active','suspended');
create type public.appointment_status as enum ('scheduled','confirmed','rescheduled','completed','no_show','canceled');
create type public.message_direction as enum ('inbound','outbound');
create type public.message_status as enum ('pending','sent','delivered','read','failed');
create type public.integration_type as enum ('whatsapp_cloud','google_ads');
create type public.integration_status as enum ('healthy','warning','error','disconnected');
create type public.automation_trigger as enum ('lead_created','lead_qualified','appointment_completed','deal_won','daily_schedule');
create type public.job_status as enum ('pending','processing','success','failed','dead_letter');

-- Helpers de auth/tenant
create or replace function public.current_user_id()
returns uuid
language sql
stable
as $$
  select auth.uid();
$$;

create or replace function public.current_tenant_id()
returns uuid
language sql
stable
as $$
  select nullif(auth.jwt()->>'active_tenant_id','')::uuid;
$$;

create or replace function public.current_role()
returns public.membership_role
language sql
stable
as $$
  select coalesce((auth.jwt()->>'app_role')::public.membership_role, 'viewer'::public.membership_role);
$$;

create or replace function public.is_superadmin()
returns boolean
language sql
stable
as $$
  select public.current_role() = 'superadmin'::public.membership_role;
$$;

create or replace function public.has_tenant_access(target_tenant uuid)
returns boolean
language sql
stable
as $$
  select exists (
    select 1
    from public.memberships m
    join public.tenants t on t.id = m.tenant_id
    where m.user_id = public.current_user_id()
      and m.tenant_id = target_tenant
      and m.is_active = true
      and t.status = 'active'
  ) or public.is_superadmin();
$$;

create or replace function public.has_min_role(target_tenant uuid, min_role public.membership_role)
returns boolean
language sql
stable
as $$
  with rank_map as (
    select * from (values
      ('viewer'::public.membership_role,1),
      ('agent'::public.membership_role,2),
      ('manager'::public.membership_role,3),
      ('owner'::public.membership_role,4),
      ('superadmin'::public.membership_role,5)
    ) as v(role_name, role_rank)
  )
  select public.is_superadmin() or exists (
    select 1
    from public.memberships m
    join rank_map current_rank on current_rank.role_name = m.role
    join rank_map required_rank on required_rank.role_name = min_role
    join public.tenants t on t.id = m.tenant_id
    where m.user_id = public.current_user_id()
      and m.tenant_id = target_tenant
      and m.is_active = true
      and t.status = 'active'
      and current_rank.role_rank >= required_rank.role_rank
  );
$$;

-- Tabelas base
create table if not exists public.tenants (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  slug text not null unique,
  niche text,
  status public.tenant_status not null default 'active',
  timezone text not null default 'America/Sao_Paulo',
  review_link text,
  settings jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists public.users (
  id uuid primary key,
  full_name text,
  phone text,
  is_platform_admin boolean not null default false,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists public.memberships (
  id uuid primary key default gen_random_uuid(),
  tenant_id uuid not null references public.tenants(id) on delete cascade,
  user_id uuid not null references public.users(id) on delete cascade,
  role public.membership_role not null,
  is_active boolean not null default true,
  created_at timestamptz not null default now(),
  unique(tenant_id, user_id)
);

create table if not exists public.pipelines (
  id uuid primary key default gen_random_uuid(),
  tenant_id uuid not null references public.tenants(id) on delete cascade,
  name text not null,
  is_default boolean not null default false,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists public.pipeline_stages (
  id uuid primary key default gen_random_uuid(),
  tenant_id uuid not null references public.tenants(id) on delete cascade,
  pipeline_id uuid not null references public.pipelines(id) on delete cascade,
  name text not null,
  order_index integer not null,
  conversion_trigger public.automation_trigger,
  created_at timestamptz not null default now(),
  unique(pipeline_id, order_index)
);

create table if not exists public.leads (
  id uuid primary key default gen_random_uuid(),
  tenant_id uuid not null references public.tenants(id) on delete cascade,
  pipeline_id uuid references public.pipelines(id),
  stage_id uuid references public.pipeline_stages(id),
  responsible_user_id uuid references public.users(id),
  name text not null,
  phone text not null,
  service_interest text,
  origin text,
  campaign text,
  term text,
  landing_page text,
  page_url text,
  referrer text,
  notes text,
  status text,
  potential_value numeric(12,2),
  last_interaction_at timestamptz,
  attribution jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists public.lead_events (
  id uuid primary key default gen_random_uuid(),
  tenant_id uuid not null references public.tenants(id) on delete cascade,
  lead_id uuid not null references public.leads(id) on delete cascade,
  actor_user_id uuid references public.users(id),
  event_type text not null,
  payload jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default now()
);

create table if not exists public.locations (
  id uuid primary key default gen_random_uuid(),
  tenant_id uuid not null references public.tenants(id) on delete cascade,
  name text not null,
  address text,
  created_at timestamptz not null default now()
);

create table if not exists public.professionals (
  id uuid primary key default gen_random_uuid(),
  tenant_id uuid not null references public.tenants(id) on delete cascade,
  user_id uuid references public.users(id),
  full_name text not null,
  specialty text,
  is_active boolean not null default true,
  created_at timestamptz not null default now()
);

create table if not exists public.services (
  id uuid primary key default gen_random_uuid(),
  tenant_id uuid not null references public.tenants(id) on delete cascade,
  name text not null,
  duration_minutes integer not null default 60,
  default_price numeric(12,2),
  created_at timestamptz not null default now()
);

create table if not exists public.appointments (
  id uuid primary key default gen_random_uuid(),
  tenant_id uuid not null references public.tenants(id) on delete cascade,
  lead_id uuid references public.leads(id),
  service_id uuid references public.services(id),
  professional_id uuid references public.professionals(id),
  location_id uuid references public.locations(id),
  status public.appointment_status not null default 'scheduled',
  starts_at timestamptz not null,
  ends_at timestamptz not null,
  notes text,
  created_by uuid references public.users(id),
  updated_by uuid references public.users(id),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists public.conversations (
  id uuid primary key default gen_random_uuid(),
  tenant_id uuid not null references public.tenants(id) on delete cascade,
  lead_id uuid references public.leads(id),
  channel text not null default 'whatsapp',
  contact_phone text,
  assigned_user_id uuid references public.users(id),
  unread_count integer not null default 0,
  last_message_at timestamptz,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists public.messages (
  id uuid primary key default gen_random_uuid(),
  tenant_id uuid not null references public.tenants(id) on delete cascade,
  conversation_id uuid not null references public.conversations(id) on delete cascade,
  external_message_id text,
  direction public.message_direction not null,
  status public.message_status not null default 'pending',
  sender text,
  recipient text,
  body text,
  payload jsonb not null default '{}'::jsonb,
  sent_at timestamptz,
  delivered_at timestamptz,
  read_at timestamptz,
  created_at timestamptz not null default now(),
  unique (tenant_id, external_message_id)
);

create table if not exists public.automations (
  id uuid primary key default gen_random_uuid(),
  tenant_id uuid not null references public.tenants(id) on delete cascade,
  name text not null,
  trigger public.automation_trigger not null,
  is_active boolean not null default true,
  config jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create table if not exists public.automation_runs (
  id uuid primary key default gen_random_uuid(),
  tenant_id uuid not null references public.tenants(id) on delete cascade,
  automation_id uuid not null references public.automations(id) on delete cascade,
  status public.job_status not null default 'pending',
  attempt integer not null default 0,
  idempotency_key text,
  payload jsonb not null default '{}'::jsonb,
  result jsonb,
  scheduled_at timestamptz not null default now(),
  executed_at timestamptz,
  created_at timestamptz not null default now(),
  unique (tenant_id, idempotency_key)
);

create table if not exists public.integrations (
  id uuid primary key default gen_random_uuid(),
  tenant_id uuid not null references public.tenants(id) on delete cascade,
  type public.integration_type not null,
  status public.integration_status not null default 'disconnected',
  config jsonb not null default '{}'::jsonb,
  secrets_ref text,
  last_heartbeat_at timestamptz,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  unique(tenant_id, type)
);

create table if not exists public.integration_logs (
  id uuid primary key default gen_random_uuid(),
  tenant_id uuid not null references public.tenants(id) on delete cascade,
  integration_id uuid references public.integrations(id) on delete set null,
  level text not null,
  event_type text not null,
  external_event_id text,
  request_payload jsonb,
  response_payload jsonb,
  error_message text,
  retry_count integer not null default 0,
  created_at timestamptz not null default now(),
  unique(tenant_id, event_type, external_event_id)
);

create table if not exists public.review_requests (
  id uuid primary key default gen_random_uuid(),
  tenant_id uuid not null references public.tenants(id) on delete cascade,
  lead_id uuid references public.leads(id),
  appointment_id uuid references public.appointments(id),
  status public.job_status not null default 'pending',
  scheduled_at timestamptz not null,
  sent_at timestamptz,
  channel text not null default 'whatsapp',
  created_at timestamptz not null default now()
);

create table if not exists public.notification_jobs (
  id uuid primary key default gen_random_uuid(),
  tenant_id uuid not null references public.tenants(id) on delete cascade,
  type text not null,
  channel text not null,
  status public.job_status not null default 'pending',
  payload jsonb not null default '{}'::jsonb,
  run_at timestamptz not null,
  processed_at timestamptz,
  attempts integer not null default 0,
  created_at timestamptz not null default now()
);

create table if not exists public.audit_logs (
  id uuid primary key default gen_random_uuid(),
  tenant_id uuid references public.tenants(id) on delete cascade,
  actor_user_id uuid references public.users(id),
  action text not null,
  entity text not null,
  entity_id uuid,
  metadata jsonb not null default '{}'::jsonb,
  ip_address inet,
  created_at timestamptz not null default now()
);

-- Índices principais
create index if not exists idx_memberships_user_tenant on public.memberships(user_id, tenant_id);
create index if not exists idx_leads_tenant_created_at on public.leads(tenant_id, created_at desc);
create index if not exists idx_lead_events_tenant_lead on public.lead_events(tenant_id, lead_id);
create index if not exists idx_appointments_tenant_start on public.appointments(tenant_id, starts_at);
create index if not exists idx_messages_tenant_conversation on public.messages(tenant_id, conversation_id, created_at desc);
create index if not exists idx_integration_logs_tenant_created on public.integration_logs(tenant_id, created_at desc);
create index if not exists idx_notification_jobs_due on public.notification_jobs(status, run_at);

-- Trigger genérico para updated_at
create or replace function public.set_updated_at()
returns trigger
language plpgsql
as $$
begin
  new.updated_at = now();
  return new;
end;
$$;

create trigger trg_tenants_updated_at before update on public.tenants for each row execute function public.set_updated_at();
create trigger trg_pipelines_updated_at before update on public.pipelines for each row execute function public.set_updated_at();
create trigger trg_leads_updated_at before update on public.leads for each row execute function public.set_updated_at();
create trigger trg_appointments_updated_at before update on public.appointments for each row execute function public.set_updated_at();
create trigger trg_conversations_updated_at before update on public.conversations for each row execute function public.set_updated_at();
create trigger trg_automations_updated_at before update on public.automations for each row execute function public.set_updated_at();
create trigger trg_integrations_updated_at before update on public.integrations for each row execute function public.set_updated_at();
create trigger trg_users_updated_at before update on public.users for each row execute function public.set_updated_at();

-- RLS
alter table public.tenants enable row level security;
alter table public.users enable row level security;
alter table public.memberships enable row level security;
alter table public.pipelines enable row level security;
alter table public.pipeline_stages enable row level security;
alter table public.leads enable row level security;
alter table public.lead_events enable row level security;
alter table public.locations enable row level security;
alter table public.professionals enable row level security;
alter table public.services enable row level security;
alter table public.appointments enable row level security;
alter table public.conversations enable row level security;
alter table public.messages enable row level security;
alter table public.automations enable row level security;
alter table public.automation_runs enable row level security;
alter table public.integrations enable row level security;
alter table public.integration_logs enable row level security;
alter table public.review_requests enable row level security;
alter table public.notification_jobs enable row level security;
alter table public.audit_logs enable row level security;

-- Policies comuns
create policy tenants_select on public.tenants
for select using (public.has_tenant_access(id));

create policy tenants_manage_superadmin on public.tenants
for all using (public.is_superadmin()) with check (public.is_superadmin());

create policy users_select_self_or_tenant on public.users
for select using (
  id = public.current_user_id()
  or exists (
    select 1 from public.memberships m
    where m.user_id = users.id and public.has_tenant_access(m.tenant_id)
  )
  or public.is_superadmin()
);

create policy memberships_select on public.memberships
for select using (public.has_tenant_access(tenant_id));

create policy memberships_manage on public.memberships
for all using (public.has_min_role(tenant_id, 'owner'))
with check (public.has_min_role(tenant_id, 'owner'));

-- Macro para tabelas tenantizadas
create policy pipelines_tenant_access on public.pipelines
for all using (public.has_tenant_access(tenant_id)) with check (public.has_tenant_access(tenant_id));
create policy pipeline_stages_tenant_access on public.pipeline_stages
for all using (public.has_tenant_access(tenant_id)) with check (public.has_tenant_access(tenant_id));
create policy leads_tenant_access on public.leads
for all using (public.has_tenant_access(tenant_id)) with check (public.has_tenant_access(tenant_id));
create policy lead_events_tenant_access on public.lead_events
for all using (public.has_tenant_access(tenant_id)) with check (public.has_tenant_access(tenant_id));
create policy locations_tenant_access on public.locations
for all using (public.has_tenant_access(tenant_id)) with check (public.has_tenant_access(tenant_id));
create policy professionals_tenant_access on public.professionals
for all using (public.has_tenant_access(tenant_id)) with check (public.has_tenant_access(tenant_id));
create policy services_tenant_access on public.services
for all using (public.has_tenant_access(tenant_id)) with check (public.has_tenant_access(tenant_id));
create policy appointments_tenant_access on public.appointments
for all using (public.has_tenant_access(tenant_id)) with check (public.has_tenant_access(tenant_id));
create policy conversations_tenant_access on public.conversations
for all using (public.has_tenant_access(tenant_id)) with check (public.has_tenant_access(tenant_id));
create policy messages_tenant_access on public.messages
for all using (public.has_tenant_access(tenant_id)) with check (public.has_tenant_access(tenant_id));
create policy automations_tenant_access on public.automations
for all using (public.has_tenant_access(tenant_id)) with check (public.has_tenant_access(tenant_id));
create policy automation_runs_tenant_access on public.automation_runs
for all using (public.has_tenant_access(tenant_id)) with check (public.has_tenant_access(tenant_id));
create policy integrations_tenant_access on public.integrations
for all using (public.has_tenant_access(tenant_id)) with check (public.has_tenant_access(tenant_id));
create policy integration_logs_tenant_access on public.integration_logs
for all using (public.has_tenant_access(tenant_id)) with check (public.has_tenant_access(tenant_id));
create policy review_requests_tenant_access on public.review_requests
for all using (public.has_tenant_access(tenant_id)) with check (public.has_tenant_access(tenant_id));
create policy notification_jobs_tenant_access on public.notification_jobs
for all using (public.has_tenant_access(tenant_id)) with check (public.has_tenant_access(tenant_id));
create policy audit_logs_tenant_access on public.audit_logs
for select using (public.has_tenant_access(tenant_id) or public.is_superadmin());
create policy audit_logs_insert on public.audit_logs
for insert with check (public.has_tenant_access(tenant_id) or public.is_superadmin());
