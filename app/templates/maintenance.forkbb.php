@extends ('layouts/main')
    <section class="f-main f-maintenance">
      <h2>{{ __('Maintenance') }}</h2>
      <p>{!! $p->maintenanceMessage !!}</p>
    </section>