<div id="_desktop_user_info">
  <div class="user-info {if $logged}logged-in{/if}">
    {if $logged}
      <!-- <a
        class="logout hidden-sm-down"
        href="{$logout_url}"
        rel="nofollow"
      >
        <i class="material-icons">&#xE7FF;</i>
        {l s='Sign out' d='Shop.Theme.Actions'}
      </a> -->
      <a
        class="account"
        href="{$my_account_url}"
        title="{l s='View my customer account' d='Shop.Theme.Customeraccount'}"
        rel="nofollow"
      >
        <i class="material-icons logged">&#xE7FF;</i>
        <span class="hidden-sm-down">{$firstName}</span>
      </a>
    {else}
      <a
        href="{$base_url}login?create_account=1" class="hidden-md-down"
        title="{l s='Create your account' d='Shop.Theme.Customeraccount'}"
        rel="nofollow"
      >
        <!-- <i class="material-icons">&#xE7FF;</i> -->
        <span class="hidden-sm-down">{l s='Register' d='Shop.Theme.Actions'}</span>
      </a>
      <a
        href="{$my_account_url}"
        title="{l s='Log in to your customer account' d='Shop.Theme.Customeraccount'}"
        rel="nofollow"
      >
         <i class="material-icons hidden-md-up">&#xE7FF;</i>
        <span class="hidden-sm-down">{l s='Sign in' d='Shop.Theme.Actions'}</span>
      </a>
      <span class="divider hidden-sm-down">|</span>
    {/if}
  </div>
</div>
