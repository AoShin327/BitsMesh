{*
 * BitsMesh Theme - Sidebar Panel
 *
 * Modern forum sidebar inspired by contemporary forum designs.
 * Displays different content based on login status.
 *
 * Required data (set by BitsmeshThemeHooks::injectSidebarData):
 * - SidebarIsLoggedIn: boolean
 * - SidebarSiteTitle: string
 * - SidebarSiteDescription: string
 * - SidebarUserCount: int
 * - SidebarUserDiscussionCount: int
 * - SidebarUserCommentCount: int
 * - SidebarUserName: string
 * - SidebarUserPhoto: string
 * - SidebarUserProfileUrl: string
 * - SidebarSignInUrl, SidebarRegisterUrl, SidebarSignOutUrl, etc.
 * - SidebarNewMembers: array
 *}

{if $SidebarIsLoggedIn}
{* ============================================
   LOGGED IN STATE - User Card
   ============================================ *}
<div class="bits-panel bits-user-card">
    <div class="bits-user-head">
        <a href="{$SidebarUserProfileUrl}" class="bits-user-avatar" title="{$SidebarUserName|escape:'html'}">
            <img src="{$SidebarUserPhoto}" alt="{$SidebarUserName|escape:'html'}" class="bits-avatar">
        </a>
        <div class="bits-user-menu">
            <a href="{$SidebarUserProfileUrl}" class="bits-username">{$SidebarUserName|escape:'html'}</a>
            <div class="bits-user-actions">
                <a href="{$SidebarSettingsUrl}" title="{t c='Settings' d='设置'}">
                    <svg class="iconpark-icon" width="16" height="16"><use href="#setting-two"></use></svg>
                </a>
                <a href="{$SidebarSignOutUrl}" title="{t c='Sign Out' d='退出'}">
                    <svg class="iconpark-icon" width="16" height="16"><use href="#logout"></use></svg>
                </a>
            </div>
        </div>
    </div>
    <div class="bits-user-stats">
        <div class="bits-stat-block">
            <a href="{$SidebarMyDiscussionsUrl}">
                <svg class="iconpark-icon" width="14" height="14"><use href="#write"></use></svg>
                <span>{t c='Topics' d='主题'} {$SidebarUserDiscussionCount}</span>
            </a>
            <a href="{$SidebarUserProfileUrl}">
                <svg class="iconpark-icon" width="14" height="14"><use href="#comments"></use></svg>
                <span>{t c='Comments' d='评论'} {$SidebarUserCommentCount}</span>
            </a>
        </div>
        <div class="bits-stat-block">
            <a href="{$SidebarBookmarksUrl}">
                <svg class="iconpark-icon" width="14" height="14"><use href="#like"></use></svg>
                <span>{t c='Bookmarks' d='收藏'}</span>
            </a>
            <a href="{$SidebarActivityUrl}">
                <svg class="iconpark-icon" width="14" height="14"><use href="#broadcast"></use></svg>
                <span>{t c='Activity' d='动态'}</span>
            </a>
        </div>
    </div>
</div>

{* New Discussion Button *}
<div class="bits-panel bits-new-discussion">
    <a href="{$SidebarNewDiscussionUrl}" class="bits-btn bits-btn-primary bits-btn-block">
        <svg class="iconpark-icon" width="16" height="16"><use href="#plus-cross"></use></svg>
        {t c="New Discussion" d="发帖"}
    </a>
</div>

{else}
{* ============================================
   GUEST STATE - Welcome Panel (NodeSeek clone)
   ============================================ *}
<div class="bits-panel bits-welcome-panel">
    <h4>{t c="Hello, stranger!" d="你好啊，陌生人!"}</h4>
    <div class="bits-welcome-text">
        {t c="Welcome.GuestMessage" d="我的朋友，看起来你是新来的，如果想参与到讨论中，点击下面的按钮！"}
    </div>
    <div class="bits-welcome-buttons">
        <a href="{$SidebarSignInUrl}" class="bits-btn" style="color:white;margin-right:5px;">{t c="Sign In" d="登录"}</a>
        <a href="{$SidebarRegisterUrl}" class="bits-btn" style="color:white">{t c="Register" d="注册"}</a>
    </div>
</div>
{/if}

{* ============================================
   Category Description Panel (Category pages only)
   NodeSeek style category intro panel
   ============================================ *}
{if $isCategoryPage && $SidebarCategoryDescription}
<div class="bits-panel bits-category-intro">
    <h4 class="bits-panel-title">
        <svg class="iconpark-icon" width="14" height="14"><use href="#road-sign-both"></use></svg>
        {t c="Category Intro" d="版块简介"}
    </h4>
    <p class="bits-category-description">{$SidebarCategoryDescription|escape:'html'}</p>
</div>
{/if}

{* ============================================
   Quick Access Panel (Both States)
   ============================================ *}
<div class="bits-panel bits-quick-access">
    <h4 class="bits-panel-title">
        <svg class="iconpark-icon" width="16" height="16"><use href="#rocket-one"></use></svg>
        {t c="Quick Access" d="快捷功能区"}
    </h4>
    <ul class="bits-quick-links">
        <li>
            <a href="{$SidebarCategoriesUrl}">
                <svg class="iconpark-icon" width="16" height="16"><use href="#all-application"></use></svg>
                <span>{t c="All Categories" d="所有版块"}</span>
            </a>
        </li>
        <li>
            <a href="{$SidebarDiscussionsUrl}">
                <svg class="iconpark-icon" width="16" height="16"><use href="#rss"></use></svg>
                <span>{t c="Recent Discussions" d="最新讨论"}</span>
            </a>
        </li>
        <li>
            <a href="{$SidebarActivityUrl}">
                <svg class="iconpark-icon" width="16" height="16"><use href="#broadcast"></use></svg>
                <span>{t c="Activity" d="动态"}</span>
            </a>
        </li>
        {if $SidebarIsLoggedIn}
        <li>
            <a href="{$SidebarMyDiscussionsUrl}">
                <svg class="iconpark-icon" width="16" height="16"><use href="#folder-focus"></use></svg>
                <span>{t c="My Discussions" d="我的讨论"}</span>
                {if $SidebarUserDiscussionCount > 0}
                <span class="bits-badge-count">{$SidebarUserDiscussionCount}</span>
                {/if}
            </a>
        </li>
        <li>
            <a href="{$SidebarBookmarksUrl}">
                <svg class="iconpark-icon" width="16" height="16"><use href="#like"></use></svg>
                <span>{t c="Bookmarks" d="收藏"}</span>
            </a>
        </li>
        {/if}
    </ul>
</div>

{* ============================================
   Community Stats Panel
   ============================================ *}
<div class="bits-panel bits-community-panel">
    <h4 class="bits-panel-title">
        <svg class="iconpark-icon" width="16" height="16"><use href="#chart-line"></use></svg>
        {t c="Community Stats" d="社区统计"}
    </h4>
    <div class="bits-community-stats">
        <div class="bits-community-count">
            {t c="Total Members" d="目前论坛共有"} <strong>{$SidebarUserCount}</strong> {t c="users" d="位成员"}
        </div>
    </div>

    {* New Members Section *}
    {if $SidebarNewMembers}
    <h4 class="bits-panel-title bits-panel-title-sub">
        <svg class="iconpark-icon" width="16" height="16"><use href="#concern"></use></svg>
        {t c="Welcome New Members" d="欢迎新用户"}
    </h4>
    <div class="bits-new-members">
        {foreach $SidebarNewMembers as $member}
        <a href="{$member.Url}" class="bits-new-member-item" title="{$member.Name|escape:'html'}">
            <img src="{$member.Photo}" alt="{$member.Name|escape:'html'}" class="bits-avatar-small">
            <span class="bits-member-name">{$member.Name|escape:'html'|truncate:10:'...'}</span>
        </a>
        {/foreach}
    </div>
    {/if}
</div>

{* ============================================
   Site Info Panel (Title + Description) - Moved to bottom
   ============================================ *}
{if $SidebarSiteDescription}
<div class="bits-panel bits-site-info">
    <h4 class="bits-panel-title">{$SidebarSiteTitle|escape:'html'}</h4>
    <p class="bits-site-description">{$SidebarSiteDescription|escape:'html'}</p>
</div>
{/if}
