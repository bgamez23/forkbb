@section ('pagination')
    @if ($p->model->pagination)
        <nav class="f-pages">
        @foreach ($p->model->pagination as $cur)
            @if ($cur[2])
          <a class="f-page active" href="{{ $cur[0] }}">{{ $cur[1] }}</a>
            @elseif ('info' === $cur[1])
          <span class="f-pinfo">{!! $cur[0] !!}</span>
            @elseif ('space' === $cur[1])
          <span class="f-page f-pspacer">{!! __('Spacer') !!}</span>
            @elseif ('prev' === $cur[1])
          <a rel="prev" class="f-page f-pprev" href="{{ $cur[0] }}">{!! __('Previous') !!}</a>
            @elseif ('next' === $cur[1])
          <a rel="next" class="f-page f-pnext" href="{{ $cur[0] }}">{!! __('Next') !!}</a>
            @else
          <a class="f-page" href="{{ $cur[0] }}">{{ $cur[1] }}</a>
            @endif
        @endforeach
        </nav>
    @endif
@endsection
@extends ('layouts/pm')
    <div class="f-nav-links">
      <div class="f-nlinks-b f-nlbpm">
    @yield ('pagination')
        <div class="f-actions-links">
        @if ($p->model->closed)
          <span class="f-act-span"><a class="f-btn f-btn-topic-closed" title="{{ __('Topic closed') }}"><span>{!! __('Topic closed') !!}</span></a></span>
        @endif
          <span class="f-act-span"><a class="f-btn f-btn-delete-dialog" title="{{ __('Delete dialogue') }}" href="{{ $p->model->linkDelete }}"><span>{!! __('Delete dialogue') !!}</span></a></span>
        @if ($p->model->canReply)
          <span class="f-act-span"><a class="f-btn f-btn-post-reply" title="{{ __('Post reply') }}" href="{{ $p->model->linkReply }}"><span>{!! __('Post reply') !!}</span></a></span>
        @endif
        </div>
      </div>
    </div>
    <section class="f-topic">
      <h2>{{ $p->model->name }}</h2>
@foreach ($p->posts as $id => $post)
    @if (empty($post->id) && $iswev = ['e' => [['Message %s was not found in the database', $id]]])
        @include ('layouts/iswev')
    @else
      <article id="p{{ $post->id }}" class="f-post @if (1 == $post->user->gender) f-user-male @elseif (2 == $post->user->gender) f-user-female @endif @if ($post->user->online) f-user-online @endif @if (1 === $post->postNumber) f-post-first @endif">
        @if ($p->enableMod && $post->postNumber > 1)
        <input id="checkbox-{{ $post->id }}" class="f-post-checkbox" type="checkbox" name="ids[{{ $post->id }}]" value="{{ $post->id }}" form="id-form-mod">
        @endif
        <header class="f-post-header">
          <h3>@if ($post->postNumber > 1) {!! __('Re') !!} @endif {{ $p->model->name }}</h3>
          <span class="f-post-posted"><time datetime="{{ \gmdate('c', $post->posted) }}">{{ dt($post->posted) }}</time></span>
        @if ($post->edited)
          <span class="f-post-edited" title="{{ __(['Last edit', $post->editor, dt($post->edited)]) }}">{!! __('Edited') !!}</span>
        @endif
          <span class="f-post-number"><a href="{{ $post->link }}" rel="bookmark">#{{ $post->postNumber }}</a></span>
        </header>
        <address class="f-post-user">
          <div class="f-post-usticky">
            <ul class="f-user-info-first">
        @if ($p->user->viewUsers && $post->user->link)
              <li class="f-username"><a href="{{ $post->user->link }}" rel="author">{{ $post->user->username }}</a></li>
        @else
              <li class="f-username">{{ $post->user->username }}</li>
        @endif
            </ul>
        @if ($p->user->showAvatar && $post->user->avatar)
            <p class="f-avatar">
              <img alt="{{ $post->user->username }}" src="{{ $post->user->avatar }}" loading="lazy">
            </p>
        @endif
            <ul class="f-user-info">
        @if ($p->user->viewUsers && $post->user->link)
              <li class="f-username"><a href="{{ $post->user->link }}" rel="author">{{ $post->user->username }}</a></li>
        @else
              <li class="f-username">{{ $post->user->username }}</li>
        @endif
              <li class="f-usertitle">{{ $post->user->title() }}</li>
        @if ($p->user->showUserInfo && $p->user->showPostCount && $post->user->num_posts)
              <li class="f-postcount">{!! __(['%s post', $post->user->num_posts, num($post->user->num_posts)]) !!}</li>
        @endif
            </ul>
        @if (! $post->user->isGuest && $p->user->showUserInfo)
            <ul class="f-user-info-add">
            @if ($p->user->isAdmMod && '' != $post->user->admin_note)
              <li class="f-admin-note" title="{{ __('Admin note') }}">{{ $post->user->admin_note }}</li>
            @endif
              <li>{!! __(['Registered: %s', dt($post->user->registered, true)]) !!}</li>
            @if ($post->user->location)
              <li>{!! __('From') !!} {{ $post->user->censorLocation }}</li>
            @endif
            </ul>
        @endif
          </div>
        </address>
        <div class="f-post-body">
          <div class="f-post-main">
            {!! $post->html() !!}
          </div>
        @if ($p->user->showSignature && $post->user->isSignature)
          <aside class="f-post-signature">
            <hr>
            {!! $post->user->htmlSign !!}
          </aside>
        @endif
        </div>
        <footer class="f-post-footer">
          <div class="f-post-footer-add">
        @if (! $post->user->isGuest)
            <span class="f-userstatus">{!! __($post->user->online ? 'Online' : 'Offline') !!}</span>
        @endif
          </div>
        @if ($post->canDelete || $post->canEdit || $post->canQuote)
          <div class="f-post-btns">
            <ul>
            @if ($post->canDelete)
              <li class="f-postdelete"><a class="f-btn" title="{{ __('Delete') }}" href="{{ $post->linkDelete }}"><span>{!! __('Delete') !!}</span></a></li>
            @endif
            @if ($post->canEdit)
              <li class="f-postedit"><a class="f-btn" title="{{ __('Edit') }}" href="{{ $post->linkEdit }}"><span>{!! __('Edit') !!}</span></a></li>
            @endif
            @if ($post->canQuote)
              <li class="f-postquote"><a class="f-btn" title="{{ __('Quote') }}" href="{{ $post->linkQuote }}"><span>{!! __('Quote') !!}</span></a></li>
            @endif
            </ul>
          </div>
        @endif
        </footer>
      </article>
    @endif
@endforeach
    </section>
    <div class="f-nav-links">
    @if ($p->form)
      <div class="f-nlinks">
    @else
      <div class="f-nlinks-a f-nlbpm">
    @endif
        <div class="f-actions-links">
          <span class="f-act-span"><a class="f-btn f-btn-delete-dialog" title="{{ __('Delete dialogue') }}" href="{{ $p->model->linkDelete }}"><span>{!! __('Delete dialogue') !!}</span></a></span>
        @if ($p->model->canReply)
          <span class="f-act-span"><a class="f-btn f-btn-post-reply" title="{{ __('Post reply') }}" href="{{ $p->model->linkReply }}"><span>{!! __('Post reply') !!}</span></a></span>
        @endif
        </div>
    @yield ('pagination')
      </div>
    </div>
@if ($form = $p->form)
    <section class="f-post-form">
      <h2>{!! __('Quick post') !!}</h2>
      <div class="f-fdiv">
    @include ('layouts/form')
      </div>
    </section>
@endif