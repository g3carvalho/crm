-- Seed básico para ambiente de desenvolvimento
insert into public.tenants (id, name, slug, niche, status, timezone)
values
  ('11111111-1111-1111-1111-111111111111','Clínica Exemplo','clinica-exemplo','saude','active','America/Sao_Paulo')
on conflict (id) do nothing;

insert into public.users (id, full_name, phone, is_platform_admin)
values
  ('22222222-2222-2222-2222-222222222222','Dono Exemplo','+5511999999999',false),
  ('33333333-3333-3333-3333-333333333333','Super Admin','+5511888888888',true)
on conflict (id) do nothing;

insert into public.memberships (tenant_id, user_id, role, is_active)
values
  ('11111111-1111-1111-1111-111111111111','22222222-2222-2222-2222-222222222222','owner',true)
on conflict (tenant_id, user_id) do nothing;

insert into public.pipelines (id, tenant_id, name, is_default)
values
  ('44444444-4444-4444-4444-444444444444','11111111-1111-1111-1111-111111111111','Funil Principal',true)
on conflict (id) do nothing;

insert into public.pipeline_stages (tenant_id, pipeline_id, name, order_index)
values
  ('11111111-1111-1111-1111-111111111111','44444444-4444-4444-4444-444444444444','Novo Lead',1),
  ('11111111-1111-1111-1111-111111111111','44444444-4444-4444-4444-444444444444','Qualificado',2),
  ('11111111-1111-1111-1111-111111111111','44444444-4444-4444-4444-444444444444','Fechado',3)
on conflict do nothing;
