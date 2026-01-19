{*
 * BitsMesh Theme - Post List Controller
 *
 * Modern forum style control bar with:
 * - Left: Sorter (New Comments / New Posts toggle)
 * - Right: Custom pager with triangle arrows (NodeSeek style)
 *
 * Required data (set by BitsmeshThemeHooks):
 * - BitsCurrentSort: string (comments|posts)
 * - BitsSortCommentsUrl: string
 * - BitsSortPostsUrl: string
 * - BitsShowPager: bool
 * - BitsCurrentPage: int
 * - BitsTotalPages: int
 * - BitsPagerPages: array [{page, url, current, ellipsis}, ...]
 * - BitsPagerPrevUrl: string
 * - BitsPagerNextUrl: string
 *}

<div class="bits-post-list-controler">
    {* Sorter (Left side) *}
    <div class="bits-sorter">
        <a href="{$BitsSortCommentsUrl}"
           class="{if $BitsCurrentSort == 'comments'}selected{/if}"
           data-sort="comments">
            {t c="New Comments" d="New Comments"}
        </a>
        <a href="{$BitsSortPostsUrl}"
           class="{if $BitsCurrentSort == 'posts'}selected{/if}"
           data-sort="posts">
            {t c="New Posts" d="New Posts"}
        </a>
    </div>

    {* Pager (Right side) - NodeSeek style *}
    {if $BitsShowPager}
    <div class="bits-pager" role="navigation" aria-label="pagination">
        {* Previous arrow *}
        {if $BitsPagerPrevUrl}
            <a href="{$BitsPagerPrevUrl}" class="bits-pager-prev" title="{t c='Previous Page' d='Previous'}">
                <div class="bits-triangle-left"></div>
            </a>
        {else}
            <span aria-disabled="true" class="bits-pager-prev disabled">
                <div class="bits-triangle-left"></div>
            </span>
        {/if}

        {* Page numbers *}
        {foreach $BitsPagerPages as $pageInfo}
            {if $pageInfo.ellipsis}
                <span class="bits-pager-pos bits-pager-ellipsis">..</span>
            {elseif $pageInfo.current}
                <span class="bits-pager-pos bits-pager-cur" aria-current="page">{$pageInfo.page}</span>
            {else}
                <a href="{$pageInfo.url}" class="bits-pager-pos" aria-label="{t c='Page %s' sprintf=$pageInfo.page}">{$pageInfo.page}</a>
            {/if}
        {/foreach}

        {* Next arrow *}
        {if $BitsPagerNextUrl}
            <a href="{$BitsPagerNextUrl}" class="bits-pager-next" title="{t c='Next Page' d='Next'}">
                <div class="bits-triangle-right"></div>
            </a>
        {else}
            <span aria-disabled="true" class="bits-pager-next disabled">
                <div class="bits-triangle-right"></div>
            </span>
        {/if}
    </div>
    {/if}
</div>
