@extends ('layouts/main')
    <section class="f-main f-login">
      <div class="f-fdiv f-lrdiv">
        <h2>{!! __('Change pass') !!}</h2>
        <form class="f-form" method="post" action="{!! $p->formAction !!}">
          <input type="hidden" name="token" value="{!! $p->formToken !!}">
          <fieldset>
            <dl>
              <dt><label class="f-child1 f-req" for="id-password">{!! __('New pass') !!}</label></dt>
              <dd>
                <input required class="f-ctrl" id="id-password" type="password" name="password" pattern="^.{16,}$" autofocus tabindex="1">
              </dd>
            </dl>
            <dl>
              <dt><label class="f-child1 f-req" for="id-password2">{!! __('Confirm new pass') !!}</label></dt>
              <dd>
                <input required class="f-ctrl" id="id-password2" type="password" name="password2" pattern="^.{16,}$" tabindex="2">
                <p class="f-child4">{!! __('Pass format') !!} {!! __('Pass info') !!}</p>
              </dd>
            </dl>
          </fieldset>
          <p class="f-btns">
            <input class="f-btn" type="submit" name="login" value="{!! __('Change passphrase') !!}" tabindex="3">
          </p>
        </form>
      </div>
    </section>