{*
 * BitsMesh Theme - Bottom Pager
 *
 * Replicates the top bits-pager at the bottom of discussion lists.
 * Uses the same data variables set by BitsmeshThemeHooks.
 *
 * Required data:
 * - BitsShowPager: bool
 * - BitsPagerPages: array [{page, url, current, ellipsis}, ...]
 * - BitsPagerPrevUrl: string
 * - BitsPagerNextUrl: string
 *}

{if $BitsShowPager}
<div class="bits-bottom-pager-wrapper">
    <div class="bits-pager bits-pager-bottom" role="navigation" aria-label="pagination">
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
</div>
{/if}
