<!-- Site Logo/Title -->
<div class="bits-site-title">
    <h1><a href="{link path="/"}">{$Title}</a></h1>
</div>

<!-- Main Navigation -->
<nav class="bits-nav-menu" aria-label="Main navigation">
    <ul>
        <li class="{if $isHomepage}current-category{/if}">
            <a href="{link path="/"}" {if $isHomepage}aria-current="page"{/if}>Home</a>
        </li>
        <li class="{if inSection('CategoryList')}current-category{/if}">
            <a href="{link path="/categories"}" {if inSection('CategoryList')}aria-current="page"{/if}>Categories</a>
        </li>
        <li class="{if inSection('DiscussionList')}current-category{/if}">
            <a href="{link path="/discussions"}" {if inSection('DiscussionList')}aria-current="page"{/if}>Discussions</a>
        </li>
        {if $User.SignedIn}
            <li>
                <a href="{link path="/activity"}">Activity</a>
            </li>
        {/if}
    </ul>
</nav>

<!-- Search Box -->
<form class="bits-search-box" role="search" action="{link path="/search"}" method="get">
    <input type="search" name="Search" id="bits-search-input" placeholder="Search..." aria-label="Search" />
</form>

<!-- User Menu -->
<div class="bits-user-menu">
    {if $User.SignedIn}
        <a href="{$User.ProfileUrl}" class="bits-user-link">
            <img class="avatar-normal" src="{$User.PhotoUrl}" alt="{$User.Name}" />
        </a>
        <a href="{link path="/entry/signout"}" class="bits-signout">Sign Out</a>
    {else}
        <a href="{link path="/entry/signin"}" class="bits-btn">Sign In</a>
    {/if}
</div>

<!-- Dark Mode Toggle -->
<button class="bits-dark-toggle" aria-label="Toggle dark mode" title="Toggle Dark Mode">
    <span aria-hidden="true">🌙</span>
</button>

<!-- Mobile Hamburger -->
<button class="bits-hamburger" aria-label="Open menu" aria-expanded="false" aria-controls="bits-mobile-nav">
    <span></span>
    <span></span>
    <span></span>
</button>
