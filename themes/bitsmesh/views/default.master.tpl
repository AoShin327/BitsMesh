<!DOCTYPE html>
<html lang="{$CurrentLocale.Key}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {asset name="Head"}
</head>

{assign var="SectionGroups" value=(isset($Groups) || isset($Group))}

<body id="{$BodyID}" class="{$BodyClass}">

    {* IconPark SVG Sprite *}
    {include file="iconpark-sprite.tpl"}

    <header class="bits-header" role="banner">
        <div id="bits-head" class="bits-container">
            {include file="partials/header.tpl"}
        </div>
    </header>

    <section id="bits-frame">
        <div id="bits-body" class="bits-container">
            <!-- Main Content -->
            <main id="bits-body-left" role="main">
                {if !$isHomepage && !$isCategoryPage}
                    <nav class="bits-breadcrumbs" aria-label="Breadcrumb">
                        {breadcrumbs}
                    </nav>
                {/if}

                <div id="bits-content">
                    {* Post List Controller (Sorter + Pager) - shown on discussion list pages *}
                    {if $BitsShowPostListControler}
                        {include file="modules/post-list-controler.tpl"}
                    {/if}

                    {asset name="Content"}
                </div>
            </main>

            <!-- Right Panel -->
            <aside id="bits-right-panel" aria-label="Sidebar">
                <!-- Custom sidebar panels (Site info, Welcome, Quick Access, Stats, Category List) -->
                {include file="modules/sidebar-welcome.tpl"}

                {asset name="Panel"}
            </aside>
        </div>
    </section>

    <footer class="bits-footer" role="contentinfo">
        {include file="partials/footer.tpl"}
    </footer>

    <!-- Fast Navigation -->
    <div id="bits-fast-nav">
        <button id="bits-back-to-top" class="bits-nav-btn" style="display:none" title="返回顶部" aria-label="返回顶部">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="18 15 12 9 6 15"></polyline>
            </svg>
        </button>
    </div>

    <div id="modals"></div>
    {event name="AfterBody"}

    <!-- Theme Scripts -->
    <script src="{asset name='js/cache.js' type='url'}"></script>
    <script src="{asset name='js/concurrency.js' type='url'}"></script>
    <script src="{asset name='js/darkMode.js' type='url'}"></script>
    <script src="{asset name='js/theme.js' type='url'}"></script>
</body>
</html>
