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

    <header class="bits-header" role="banner">
        <div id="bits-head" class="bits-container">
            {include file="partials/header.tpl"}
        </div>
    </header>

    <!-- Left Panel - Categories (Large screens only) -->
    <aside id="bits-left-panel" aria-label="Category navigation">
        <nav class="bits-category-list">
            {categories_module}
        </nav>
    </aside>

    <section id="bits-frame">
        <div id="bits-body" class="bits-container">
            <!-- Main Content -->
            <main id="bits-body-left" role="main">
                {if !$isHomepage}
                    <nav class="bits-breadcrumbs" aria-label="Breadcrumb">
                        {breadcrumbs}
                    </nav>
                {/if}

                <div id="bits-content">
                    {asset name="Content"}
                </div>
            </main>

            <!-- Right Panel -->
            <aside id="bits-right-panel" aria-label="Sidebar">
                <!-- Category list for medium screens -->
                <nav class="bits-category-list">
                    {categories_module}
                </nav>

                {if !$SectionGroups}
                    <div class="bits-panel bits-search-panel">
                        <h4>Search</h4>
                        {searchbox}
                    </div>
                {/if}

                {asset name="Panel"}
            </aside>
        </div>
    </section>

    <footer class="bits-footer" role="contentinfo">
        {include file="partials/footer.tpl"}
    </footer>

    <!-- Fast Navigation -->
    <div id="bits-fast-nav">
        <button id="bits-back-to-top" class="bits-nav-btn" style="display:none" title="Back to Top" aria-label="Back to top">
            <span aria-hidden="true">↑</span>
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
