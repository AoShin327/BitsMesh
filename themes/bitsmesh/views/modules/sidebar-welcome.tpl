{*
 * BitsMesh Theme - Sidebar Panel
 *
 * Modern forum sidebar inspired by contemporary forum designs.
 * Displays different content based on login status.
 *
 * Required data (set by BitsmeshThemeHooks::injectSidebarData)
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
 * - SidebarCategories: array (from injectCategoryListData)
 *
 * Panel Order:
 * - Guest: Welcome → Site Info → Quick Access → Categories → Community Stats
 * - Logged In: User Card → New Discussion → Site Info → Quick Access → Categories → Community Stats
 *}

{if $SidebarIsLoggedIn}
{* ============================================
   LOGGED IN STATE - User Card (Modern forum style pixel-perfect replica)
   ============================================ *}
<div class="bits-panel bits-user-card">
    {* User Head: Avatar + Menu (flex layout) *}
    <div class="bits-user-head">
        <a href="{$SidebarUserProfileUrl}" title="{$SidebarUserName|escape:'html'}">
            <img src="{$SidebarUserPhoto}" alt="{$SidebarUserName|escape:'html'}" class="bits-avatar-normal">
        </a>
        <div class="bits-user-menu">
            <a href="{$SidebarUserProfileUrl}" class="bits-username">{$SidebarUserName|escape:'html'}</a>
            <div class="bits-user-actions">
                <a href="/board" title="{t c='Check In' d='签到'}">
                    <svg class="iconpark-icon"><use href="#plan"></use></svg>
                </a>
                <a href="/activity" title="{t c='Activity' d='动态'}">
                    <svg class="iconpark-icon"><use href="#dashboard-one"></use></svg>
                </a>
                <a href="{$SidebarSettingsUrl}" title="{t c='Settings' d='设置'}">
                    <svg class="iconpark-icon"><use href="#setting-two"></use></svg>
                </a>
                <a href="{$SidebarSignOutUrl}" title="{t c='Sign Out' d='登出'}">
                    <svg class="iconpark-icon"><use href="#logout"></use></svg>
                </a>
            </div>
        </div>
    </div>
    {* User Stats Card (Two columns - modern forum style) *}
    <div class="bits-user-stat">
        <div class="bits-stat-block">
            <div>
                <a href="/progress">
                    <svg class="iconpark-icon"><use href="#level"></use></svg>
                    <span>{t c='Level' d='等级'} Lv {$SidebarUserLevel|default:1}</span>
                </a>
            </div>
            <div>
                <a href="/credit">
                    <svg class="iconpark-icon"><use href="#chicken-leg"></use></svg>
                    <span>{t c='Credits' d='鸡腿'} {$SidebarUserPoints|default:0}</span>
                </a>
            </div>
            <div>
                <a href="{$SidebarFollowingUrl}">
                    <svg class="iconpark-icon"><use href="#star"></use></svg>
                    <span>{t c='Following' d='关注'} {$SidebarUserFollowingCount|default:0}</span>
                </a>
            </div>
            <div>
                <a href="/notification">
                    <svg class="iconpark-icon"><use href="#remind"></use></svg>
                    <span>{t c='Notifications' d='通知'} {$SidebarUnreadNotifications|default:0}</span>
                </a>
            </div>
        </div>
        <div class="bits-stat-block">
            <div>
                <a href="{$SidebarUserSpaceUrl}/thread">
                    <svg class="iconpark-icon"><use href="#write"></use></svg>
                    <span>{t c='Topics' d='主题帖'} {$SidebarUserDiscussionCount|default:0}</span>
                </a>
            </div>
            <div>
                <a href="{$SidebarUserSpaceUrl}/post">
                    <svg class="iconpark-icon"><use href="#comments"></use></svg>
                    <span>{t c='Comments' d='评论数'} {$SidebarUserCommentCount|default:0}</span>
                </a>
            </div>
            <div>
                <a href="{$SidebarFollowersUrl}">
                    <svg class="iconpark-icon"><use href="#concern"></use></svg>
                    <span>{t c='Followers' d='粉丝'} {$SidebarUserFollowersCount|default:0}</span>
                </a>
            </div>
            <div>
                <a href="{$SidebarBookmarksUrl}">
                    <svg class="iconpark-icon"><use href="#folder-focus"></use></svg>
                    <span>{t c='Bookmarks' d='收藏'} {$SidebarUserBookmarkCount|default:0}</span>
                </a>
            </div>
        </div>
    </div>
</div>

{* New Discussion Button *}
<div class="bits-new-discussion-btn">
    <a href="{$SidebarNewDiscussionUrl}" class="bits-btn-new-discussion">
        <svg class="iconpark-icon"><use href="#plus-cross"></use></svg>
        <span>{t c="New Discussion" d="发帖"}</span>
    </a>
</div>

{* Site Info Panel - Third for logged-in users *}
{if $SidebarSiteDescription}
<div class="bits-panel bits-site-info">
    <h4 class="bits-panel-title">{$SidebarSiteTitle|escape:'html'}</h4>
    <p class="bits-site-description">{$SidebarSiteDescription|escape:'html'}</p>
</div>
{/if}

{else}
{* ============================================
   GUEST STATE - Welcome Panel (Top of sidebar for guests)
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

{* Site Info Panel - Second for guests *}
{if $SidebarSiteDescription}
<div class="bits-panel bits-site-info">
    <h4 class="bits-panel-title">{$SidebarSiteTitle|escape:'html'}</h4>
    <p class="bits-site-description">{$SidebarSiteDescription|escape:'html'}</p>
</div>
{/if}
{/if}

{* ============================================
   Category Description Panel (Category pages only)
   Modern forum category intro panel
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
        <svg class="iconpark-icon"><use href="#rocket-one"></use></svg>
        <span>{t c="Quick Access" d="快捷功能区"}</span>
    </h4>
    <ul class="bits-quick-links" role="nav">
        <li>
            <a href="#">
                <svg class="iconpark-icon"><use href="#diamonds"></use></svg>
                <span>{t c="Featured" d="推荐阅读"}</span>
            </a>
        </li>
        <li>
            <a href="#">
                <svg class="iconpark-icon"><use href="#balance-two"></use></svg>
                <span>{t c="Moderation Log" d="管理记录"}</span>
            </a>
        </li>
        <li>
            <a href="/lottery">
                <svg class="iconpark-icon"><use href="#gift"></use></svg>
                <span>{t c="Lucky Draw" d="幸运抽奖"}</span>
            </a>
        </li>
        <li>
            <a href="/invite">
                <svg class="iconpark-icon"><use href="#key"></use></svg>
                <span>{t c="Invite Friends" d="邀请好友"}</span>
            </a>
        </li>
        <li>
            <a href="#">
                <svg class="iconpark-icon"><use href="#cooperative-handshake"></use></svg>
                <span>{t c="Partners" d="合作商家"}</span>
            </a>
        </li>
        <li>
            <a href="#">
                <svg class="iconpark-icon"><use href="#link"></use></svg>
                <span>{t c="Friend Links" d="友站链接"}</span>
            </a>
        </li>
    </ul>
</div>

{* ============================================
   Category List Panel (Modern forum style)
   ============================================ *}
{if $SidebarCategories}
<div class="bits-panel bits-category-panel">
    <h4 class="bits-panel-title">
        <svg class="iconpark-icon" width="14" height="14"><use href="#all-application"></use></svg>
        <a href="{$SidebarCategoriesUrl}">{t c="All Categories" d="所有版块"}</a>
    </h4>
    <ul class="bits-category-links">
        {foreach $SidebarCategories as $category}
        <li>
            <a href="{$category.Url}">
                <svg class="iconpark-icon" width="14" height="14"><use href="#{$category.IconID}"></use></svg>
                <span>{$category.Name|escape:'html'}</span>
            </a>
        </li>
        {/foreach}
    </ul>
</div>
{/if}

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

