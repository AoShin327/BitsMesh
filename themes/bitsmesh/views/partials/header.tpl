<!-- Site Logo + Title -->
<div class="bits-site-brand">
    <a href="{link path="/"}" class="bits-brand-link">
        {assign var="logoUrl" value="{site_logo_url}"}
        {if $logoUrl}
            <img src="{$logoUrl}" alt="{site_title}" class="bits-site-logo" />
        {/if}
        <span class="bits-site-title">{site_title}</span>
    </a>
</div>

<!-- Main Navigation - Category Links -->
<nav class="bits-nav-menu" aria-label="Main navigation">
    <ul>
        {category_menu}
    </ul>
</nav>

<!-- Search Box -->
<div class="bits-search-box">
    {searchbox placeholder="{t c='搜索...'}"}
</div>

<!-- User Menu -->
<div class="bits-user-menu">
    {if $User.SignedIn}
        <a href="{$User.ProfileUrl}" class="bits-user-link">
            <img class="avatar-normal" src="{$User.PhotoUrl}" alt="{$User.Name}" />
        </a>
        <a href="{link path="/entry/signout"}" class="bits-signout">{t c="退出"}</a>
    {else}
        <a href="{link path="/entry/signin"}" class="bits-btn">{t c="登录"}</a>
    {/if}
</div>

<!-- Dark Mode Toggle -->
<button class="bits-dark-toggle" aria-label="{t c='切换深色模式'}" title="{t c='切换深色模式'}">
    <svg class="icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
    </svg>
    <svg class="icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="5"></circle>
        <line x1="12" y1="1" x2="12" y2="3"></line>
        <line x1="12" y1="21" x2="12" y2="23"></line>
        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
        <line x1="1" y1="12" x2="3" y2="12"></line>
        <line x1="21" y1="12" x2="23" y2="12"></line>
        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
    </svg>
</button>

<!-- Mobile Hamburger -->
<button class="bits-hamburger" aria-label="{t c='打开菜单'}" aria-expanded="false" aria-controls="bits-mobile-nav">
    <span></span>
    <span></span>
    <span></span>
</button>
