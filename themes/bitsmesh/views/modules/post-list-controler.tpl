{*
 * BitsMesh Theme - Post List Controller
 *
 * Modern forum style control bar with:
 * - Left: Sorter (New Comments / New Posts toggle)
 * - Right: Custom pager with triangle arrows
 *
 * Required data (set by BitsmeshThemeHooks):
 * - BitsCurrentSort: string (comments|posts)
 * - BitsSortCommentsUrl: string
 * - BitsSortPostsUrl: string
 * - BitsCurrentPage: int
 * - BitsTotalPages: int
 * - BitsPagerBaseUrl: string
 * - BitsShowPager: bool
 *}

<div class="bits-post-list-controler">
    {* Sorter (Left side) *}
    <div class="bits-sorter">
        <a href="{$BitsSortCommentsUrl}"
           class="{if $BitsCurrentSort == 'comments'}selected{/if}"
           data-sort="comments">
            {t c="New Comments" d="新评论"}
        </a>
        <a href="{$BitsSortPostsUrl}"
           class="{if $BitsCurrentSort == 'posts'}selected{/if}"
           data-sort="posts">
            {t c="New Posts" d="新帖子"}
        </a>
    </div>

    {* Pager (Right side) - Simple version *}
    {if $BitsShowPager && $BitsTotalPages > 1}
    <div class="bits-pager">
        {* Previous arrow *}
        {if $BitsCurrentPage > 1}
            <a href="{$BitsPagerBaseUrl|replace:'{Page}':'p1'}"
               class="bits-pager-arrow bits-pager-prev"
               title="{t c='Previous Page' d='上一页'}"></a>
        {else}
            <span class="bits-pager-arrow bits-pager-prev disabled"></span>
        {/if}

        {* Current page indicator *}
        <span class="bits-pager-cur">{$BitsCurrentPage}</span>
        <span class="bits-pager-ellipsis">/</span>
        <span>{$BitsTotalPages}</span>

        {* Next arrow *}
        {if $BitsCurrentPage < $BitsTotalPages}
            <a href="{$BitsPagerBaseUrl|replace:'{Page}':'p'|cat:$BitsTotalPages}"
               class="bits-pager-arrow bits-pager-next"
               title="{t c='Next Page' d='下一页'}"></a>
        {else}
            <span class="bits-pager-arrow bits-pager-next disabled"></span>
        {/if}
    </div>
    {/if}
</div>
