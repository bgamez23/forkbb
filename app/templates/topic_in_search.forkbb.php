@section ('crumbs')
      <ul class="f-crumbs">
  @foreach ($p->crumbs as $cur)
        <li class="f-crumb"><!-- inline -->
    @if ($cur[0])
          <a href="{!! $cur[0] !!}" @if ($cur[2]) class="active" @endif>{{ $cur[1] }}</a>
    @else
          <span @if ($cur[2]) class="active" @endif>{{ $cur[1] }}</span>
    @endif
        </li><!-- endinline -->
  @endforeach
      </ul>
@endsection
@section ('pagination')
  @if ($p->model->pagination)
        <nav class="f-pages">
    @foreach ($p->model->pagination as $cur)
      @if ($cur[2])
          <a class="f-page active" href="{!! $cur[0] !!}">{{ $cur[1] }}</a>
      @elseif ('info' === $cur[1])
          <span class="f-pinfo">{!! $cur[0] !!}</span>
      @elseif ('space' === $cur[1])
          <span class="f-page f-pspacer">{!! __('Spacer') !!}</span>
      @elseif ('prev' === $cur[1])
          <a rel="prev" class="f-page f-pprev" href="{!! $cur[0] !!}">{!! __('Previous') !!}</a>
      @elseif ('next' === $cur[1])
          <a rel="next" class="f-page f-pnext" href="{!! $cur[0] !!}">{!! __('Next') !!}</a>
      @else
          <a class="f-page" href="{!! $cur[0] !!}">{{ $cur[1] }}</a>
      @endif
    @endforeach
        </nav>
  @endif
@endsection
@extends ('layouts/main')
    <div class="f-nav-links">
@yield ('crumbs')
@if ($p->model->pagination)
      <div class="f-nlinks-b">
  @yield ('pagination')
      </div>
@endif
    </div>
    <section class="f-main f-topic">
      <h2>{{ $p->model->name }}</h2>
@foreach ($p->posts as $id => $post)
  @if (empty($post->id) && $iswev = ['e' => [__('Message %s was not found in the database', $id)]])
    @include ('layouts/iswev')
  @else
      <article id="p{!! $post->id !!}" class="f-post f-post-search @if ($post->user->gender == 1) f-user-male @elseif ($post->user->gender == 2) f-user-female @endif @if ($post->user->online) f-user-online @endif">
        <header class="f-post-header clearfix">
          <h3>
            <span class="f-psh-forum"><a href="{!! $post->parent->parent->link !!}" title="{!! __('Go to forum') !!}">{{ $post->parent->parent->forum_name }}</a></span>
            <span class="f-psh-topic"><a href="{!! $post->parent->link !!}" title="{!! __('Go to topic') !!}">@if ($post->id !== $post->parent->first_post_id) {!! __('Re') !!} @endif {{ cens($post->parent->subject) }}</a></span>
            <span class="f-post-posted"><a href="{!! $post->link !!}" title="{!! __('Go to post') !!}" rel="bookmark"><time datetime="{{ utc($post->posted) }}">{{ dt($post->posted) }}</time></a></span>
          </h3>
          <span class="f-post-number">#{!! $post->postNumber !!}</span>
        </header>
        <div class="f-post-body clearfix">
          <address class="f-post-left">
            <ul class="f-user-info">
    @if ($p->user->viewUsers && $post->user->link)
              <li class="f-username"><a href="{!! $post->user->link !!}">{{ $post->user->username }}</a></li>
    @else
              <li class="f-username">{{ $post->user->username }}</li>
    @endif
              <li class="f-usertitle">{{ $post->user->title() }}</li>
            </ul>
            <ul class="f-post-search-info">
              <li class="f-psi-forum">{!! __('Forum') !!}: <a href="{!! $post->parent->parent->link !!}">{{ $post->parent->parent->forum_name }}</a></li>
              <li class="f-psi-topic">{!! __('Topic') !!}: <a href="{!! $post->parent->link !!}">{{ cens($post->parent->subject) }}</a></li>
              <li class="f-psi-reply">{!! __('%s Reply', $post->parent->num_replies, num($post->parent->num_replies)) !!}</li>
    @if ($post->parent->showViews)
              <li class="f-psi-view">{!! __('%s View', $post->parent->num_views, num($post->parent->num_views)) !!}</li>
    @endif
            </ul>
          </address>
          <div class="f-post-right f-post-main">
            {!! $post->html() !!}
          </div>
        </div>
        <footer class="f-post-footer clearfix">
          <div class="f-post-left">
            <span></span>
          </div>
          <div class="f-post-right">
            <ul>
              <li class="f-posttotopic"><a class="f-btn" href="{!! $post->parent->link !!}">{!! __('Go to topic') !!}</a></li>
              <li class="f-posttopost"><a class="f-btn" href="{!! $post->link !!}">{!! __('Go to post') !!}</a></li>
            </ul>
          </div>
        </footer>
      </article>
  @endif
@endforeach
    </section>
    <div class="f-nav-links">
@if ($p->model->pagination)
      <div class="f-nlinks-a">
  @yield ('pagination')
      </div>
@endif
@yield ('crumbs')
    </div>