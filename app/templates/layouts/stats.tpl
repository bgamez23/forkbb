    <section class="f-stats">
      <h2>{!! __('Stats info') !!}</h2>
      <div class="clearfix">
@if ($p->stats)
        <dl class="right">
          <dt>{!! __('Board stats') !!}</dt>
          <dd>{!! __('No of users') !!} <strong>{!! num($p->stats->userTotal) !!}</strong></dd>
          <dd>{!! __('No of topics') !!} <strong>{!! num($p->stats->topicTotal) !!}</strong></dd>
          <dd>{!! __('No of posts') !!} <strong>{!! num($p->stats->postTotal) !!}</strong></dd>
        </dl>
@endif
        <dl class="left">
          <dt>{!! __('User info') !!}</dt>
@if ($p->stats)
  @if (is_string($p->stats->userLast))
          <dd>{!! __('Newest user')  !!} {{ $p->stats->userLast }}</dd>
  @else
          <dd>{!! __('Newest user')  !!} <a href="{!! $p->stats->userLast[0] !!}">{{ $p->stats->userLast[1] }}</a></dd>
  @endif
@endif
@if ($p->online)
          <dd>{!! __('Visitors online', num($p->online->numUsers), num($p->online->numGuests)) !!}</dd>
@endif
@if ($p->stats)
          <dd>{!! __('Most online', num($p->online->maxNum), dt($p->online->maxTime)) !!}</dd>
@endif
        </dl>
@if ($p->online && $p->online->info)
        <dl class="f-inline f-onlinelist"><!-- inline -->
          <dt>{!! __('Online users') !!}</dt>
  @foreach ($p->online->info as $cur)
    @if (is_string($cur))
          <dd>{{ $cur }}</dd>
    @else
          <dd><a href="{!! $cur[0] !!}">{{ $cur[1] }}</a></dd>
    @endif
  @endforeach
        </dl><!-- endinline -->
@endif
      </div>
    </section>