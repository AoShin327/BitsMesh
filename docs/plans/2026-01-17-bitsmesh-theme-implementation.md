# BitsMesh Theme Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Create a NodeSeek-style theme for Vanilla Forums with configurable colors, dark mode, and responsive three-column layout.

**Architecture:** Pure theme approach with deep customization of Vanilla core views. CSS variables for theming, localStorage for dark mode persistence, Playwright for testing.

**Tech Stack:** PHP 8.0+, MySQL 5.7, Smarty Templates, Vanilla CSS (no preprocessor initially), ES6 JavaScript, Playwright

**Design Document:** `docs/plans/2026-01-17-bitsmesh-theme-design.md`

**Dev URL:** http://localhost:8357/

---

## Phase 1: Theme Foundation

### Task 1.1: Create Theme Directory Structure

**Files:**
- Create: `themes/nodeseek/addon.json`
- Create: `themes/nodeseek/screenshot.png` (placeholder)

**Step 1: Create theme directory**

Run:
```bash
mkdir -p /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/{design,js,views/partials,settings,tests/e2e,tests/visual/screenshots}
```

**Step 2: Create addon.json**

Create file `themes/nodeseek/addon.json`:
```json
{
    "key": "nodeseek",
    "name": "BitsMesh Theme",
    "description": "A NodeSeek-style theme for Vanilla Forums with configurable colors and dark mode support.",
    "version": "1.0.0",
    "type": "theme",
    "license": "GPL-2.0-only",
    "author": [
        {
            "name": "BitsMesh",
            "url": "https://bitsmesh.com"
        }
    ],
    "require": {
        "vanilla": ">=3.0"
    },
    "layout": {
        "categories": "modern",
        "discussions": "modern"
    },
    "sites": [],
    "priority": 1000
}
```

**Step 3: Create placeholder screenshot**

Run:
```bash
cp /Users/kilmu/Dev/WebDev/vanilla/themes/theme-boilerplate/screenshot.png /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/screenshot.png
```

**Step 4: Verify theme directory**

Run:
```bash
ls -la /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/
```
Expected: Directory with addon.json, screenshot.png, and subdirectories

---

### Task 1.2: Create CSS Variables System

**Files:**
- Create: `themes/nodeseek/design/variables.css`

**Step 1: Create variables.css**

Create file `themes/nodeseek/design/variables.css`:
```css
/* BitsMesh Theme - CSS Variables */
/* These can be overridden by theme configuration */

:root {
    /* Primary Colors */
    --bits-primary: #2ea44f;
    --bits-secondary: #45ca6b;

    /* Text Colors */
    --bits-text: #333;
    --bits-text-fade: #555;
    --bits-text-gray: #888;

    /* Link Colors */
    --bits-link: #555;
    --bits-link-hover: #333;

    /* Background Colors */
    --bits-bg-main: #fff;
    --bits-bg-sub: #fbfbfb;
    --bits-glass: rgba(0, 0, 0, 0.05);

    /* Layout */
    --bits-container-width: 1080px;
    --bits-left-panel-width: 150px;
    --bits-right-panel-width: 260px;
    --bits-border-radius: 1rem;

    /* Shadows */
    --bits-shadow: 0 3px 8px rgba(0, 0, 0, 0.24);
    --bits-shadow-light: 0 0 5px rgba(0, 0, 0, 0.1);
}

/* Dark Mode Variables */
body.dark-layout {
    --bits-text: #aaa;
    --bits-text-fade: #999;
    --bits-text-gray: #777;

    --bits-link: #c5c5c5;
    --bits-link-hover: #fcfcfc;

    --bits-bg-main: #272727;
    --bits-bg-sub: #3b3b3b;
    --bits-glass: rgba(255, 255, 255, 0.05);

    --bits-shadow: 0 0 8px rgba(0, 0, 0, 0.7);
    --bits-shadow-light: 0 0 5px rgba(0, 0, 0, 0.4);
}
```

**Step 2: Verify file created**

Run:
```bash
cat /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/design/variables.css | head -20
```
Expected: CSS variables content displayed

---

### Task 1.3: Create Base Styles

**Files:**
- Create: `themes/nodeseek/design/bits-base.css`

**Step 1: Create bits-base.css with reset and typography**

Create file `themes/nodeseek/design/bits-base.css`:
```css
/* BitsMesh Theme - Base Styles */
@import url('variables.css');

/* Reset */
a, abbr, acronym, address, applet, big, caption, cite, dd, del, dfn, div, dl, dt,
em, fieldset, font, form, html, iframe, img, ins, kbd, label, legend, li, object,
ol, q, s, samp, small, span, strike, sub, sup, table, tbody, td, tfoot, th, thead,
tr, tt, ul, var {
    border: 0;
    font-size: inherit;
    margin: 0;
    outline: 0;
    padding: 0;
    vertical-align: baseline;
}

/* Base Body */
body {
    background-color: var(--bits-bg-sub);
    color: var(--bits-text);
    font-family: -apple-system, BlinkMacSystemFont, "Helvetica Neue", "PingFang SC",
        "Microsoft YaHei", "Source Han Sans SC", "Noto Sans CJK SC",
        "WenQuanYi Micro Hei", sans-serif;
    font-size: 14px;
    line-height: 1.5;
    margin: 0;
    padding: 0;
}

/* Links */
a {
    color: var(--bits-link);
    text-decoration: none;
    transition: color 0.2s ease;
}

a:hover {
    color: var(--bits-link-hover);
}

/* Typography */
h1, h2, h3, h4, h5, h6 {
    font-weight: 700;
    margin: 5px 0;
    color: var(--bits-text);
}

h1 { font-size: 1.5em; }
h2 { font-size: 1.25em; }
h3, h4 { font-size: 1.1em; }
h5, h6 { font-size: 1em; }

p {
    margin: 5px 0;
}

/* Lists */
ol, ul {
    list-style: none;
}

/* Form Elements */
input, textarea {
    outline: none;
}

/* Tables */
table {
    border-collapse: separate;
    border-spacing: 0;
}

/* Utility Classes */
.bits-container {
    box-sizing: border-box;
    margin: auto;
    max-width: var(--bits-container-width);
    width: 100%;
}

@media screen and (max-width: 1200px) {
    .bits-container {
        margin: 0 20px;
        width: auto;
    }
}

@media screen and (max-width: 500px) {
    .bits-container {
        margin: 0;
    }
}
```

**Step 2: Verify file created**

Run:
```bash
wc -l /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/design/bits-base.css
```
Expected: ~90 lines

---

### Task 1.4: Create Layout Styles

**Files:**
- Create: `themes/nodeseek/design/bits-layout.css`

**Step 1: Create bits-layout.css with three-column layout**

Create file `themes/nodeseek/design/bits-layout.css`:
```css
/* BitsMesh Theme - Layout Styles */

/* Main Frame */
#bits-frame {
    background-color: transparent;
    margin: 0 auto;
    min-height: 100vh;
    overflow: hidden;
    position: relative;
    width: 100%;
}

/* Header */
header.bits-header {
    background-color: var(--bits-bg-main);
    box-shadow: var(--bits-shadow-light);
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 100;
    transition: background-color 0.5s;
}

@media screen and (max-width: 500px) {
    header.bits-header {
        position: relative;
    }
}

/* Header Content */
#bits-head {
    align-items: center;
    display: flex;
    height: 40px;
    max-width: var(--bits-container-width);
    margin: 0 auto;
    padding: 0 20px;
}

@media screen and (max-width: 500px) {
    #bits-head {
        height: 30px;
    }
}

/* Body Container - Three Column Layout */
#bits-body {
    background-color: var(--bits-bg-main);
    border-radius: var(--bits-border-radius);
    box-shadow: var(--bits-shadow);
    box-sizing: border-box;
    display: flex;
    margin: 55px auto 20px;
    max-width: var(--bits-container-width);
    padding: 25px 30px;
    position: relative;
    transition: background-color 0.5s;
}

@media screen and (max-width: 500px) {
    #bits-body {
        border-radius: 4px;
        margin: 9px 4px 20px;
        padding: 20px 3px;
    }
}

/* Left Panel - Category Navigation */
#bits-left-panel {
    display: none;
    position: fixed;
    left: calc(50% - 540px - 80px);
    top: 55px;
    width: var(--bits-left-panel-width);
}

@media (min-width: 1360px) {
    #bits-left-panel {
        display: block;
    }
}

.bits-category-list {
    background-color: var(--bits-bg-main);
    border-radius: 12px;
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.08);
    font-size: 14px;
    overflow: hidden;
    width: 100px;
    margin-left: auto;
}

.bits-category-list ul {
    list-style: none;
    margin: 0;
    text-align: center;
}

.bits-category-list li a {
    align-items: center;
    color: #666;
    display: flex;
    justify-content: center;
    padding: 6px 0;
    transition: background 0.2s, color 0.2s;
}

.bits-category-list li a:hover,
.bits-category-list li.current-category a {
    background: #f1f3f5;
    color: var(--bits-primary);
}

body.dark-layout .bits-category-list li a:hover,
body.dark-layout .bits-category-list li.current-category a {
    background-color: rgba(0, 0, 0, 0.15);
}

/* Main Content Area */
#bits-body-left {
    box-sizing: border-box;
    flex: 1;
    min-width: 0;
    padding-right: 20px;
}

@media screen and (max-width: 500px) {
    #bits-body-left {
        padding-right: 0;
    }
}

/* Right Panel */
#bits-right-panel {
    flex: 0 0 var(--bits-right-panel-width);
}

@media screen and (max-width: 1200px) {
    #bits-right-panel {
        flex: 0 0 200px;
    }
}

@media screen and (max-width: 800px) {
    #bits-right-panel {
        display: none;
    }
}

/* Right Panel - Category List (shown on medium screens) */
#bits-right-panel .bits-category-list {
    display: none;
}

@media (max-width: 1359px) and (min-width: 801px) {
    #bits-right-panel .bits-category-list {
        display: block;
        width: 100%;
        margin-bottom: 10px;
    }
}

/* Panel Box */
.bits-panel {
    border-radius: 1px;
    box-shadow: var(--bits-shadow-light);
    color: var(--bits-text-gray);
    margin: 0 0 10px;
    padding: 6px 10px;
}

.bits-panel h4 {
    color: var(--bits-text);
    margin: 0;
}

.bits-panel ul {
    padding-left: 10px;
}

.bits-panel ul li {
    height: 24px;
    line-height: 24px;
    padding-left: 10px;
}

.bits-panel ul li:not(:last-child) {
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}

/* Footer */
footer.bits-footer {
    background-color: #333;
    color: #999;
    padding: 20px 0;
    width: 100%;
}

footer.bits-footer a {
    color: #999;
}

footer.bits-footer a:hover {
    color: #fff;
}
```

**Step 2: Verify file created**

Run:
```bash
wc -l /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/design/bits-layout.css
```
Expected: ~180 lines

---

### Task 1.5: Create Header Styles

**Files:**
- Create: `themes/nodeseek/design/bits-header.css`

**Step 1: Create bits-header.css**

Create file `themes/nodeseek/design/bits-header.css`:
```css
/* BitsMesh Theme - Header Styles */

/* Site Title */
.bits-site-title {
    font-family: "Helvetica Neue", helvetica, arial, sans-serif;
    font-size: 24px;
    font-weight: 700;
    margin-right: 6px;
}

.bits-site-title a {
    color: var(--bits-text);
}

/* Navigation Menu */
.bits-nav-menu {
    align-items: center;
    display: flex;
    height: 40px;
    margin-left: 0;
}

@media screen and (max-width: 500px) {
    .bits-nav-menu {
        flex: 1;
        font-size: 14px;
        height: 30px;
    }
}

.bits-nav-menu li {
    margin: 0 8px;
}

@media screen and (max-width: 500px) {
    .bits-nav-menu li {
        margin: 0 5px;
    }
}

.bits-nav-menu .current-category {
    font-weight: 700;
}

/* Search Box */
.bits-search-box {
    flex: 0 1 170px;
    margin-left: auto;
    max-width: 290px;
    position: relative;
    transition: all 0.5s ease-in-out;
}

.bits-search-box:hover {
    flex: 1 1 170px;
}

.bits-search-box input[type="text"] {
    background-color: var(--bits-bg-sub);
    border: 1px solid var(--bits-glass);
    border-radius: 4px;
    box-sizing: border-box;
    color: var(--bits-text);
    font-size: 14px;
    height: 28px;
    padding: 0 30px 0 10px;
    transition: all 0.5s ease-in-out;
    width: 100%;
}

.bits-search-box input[type="text"]:focus {
    border-color: var(--bits-primary);
    outline: none;
}

body.dark-layout .bits-search-box input[type="text"] {
    background-color: #555;
    border-color: #666;
}

/* User Menu */
.bits-user-menu {
    display: flex;
    align-items: center;
    margin-left: 10px;
}

.bits-user-menu .avatar-normal {
    border-radius: 15%;
    box-shadow: 0 0 3px rgba(0, 0, 0, 0.1);
    height: 30px;
    width: 30px;
}

/* Dark Mode Toggle */
.bits-dark-toggle {
    color: var(--bits-link);
    cursor: pointer;
    padding: 0 10px;
    font-size: 18px;
}

.bits-dark-toggle:hover {
    color: var(--bits-link-hover);
}

/* Mobile Navigation */
.bits-mobile-nav {
    display: none;
}

@media screen and (max-width: 500px) {
    .bits-mobile-nav {
        background-color: var(--bits-bg-main);
        border-radius: 4px;
        box-shadow: var(--bits-shadow-light);
        display: flex;
        flex-wrap: wrap;
        margin: 9px 4px 0;
    }

    .bits-mobile-nav li {
        margin: 3px 6px;
    }
}

/* Hamburger Menu Button */
.bits-hamburger {
    display: none;
    cursor: pointer;
    padding: 5px;
}

@media screen and (max-width: 500px) {
    .bits-hamburger {
        display: block;
    }
}

.bits-hamburger span {
    background-color: var(--bits-text);
    display: block;
    height: 2px;
    margin: 4px 0;
    width: 20px;
    transition: all 0.3s;
}
```

**Step 2: Verify file created**

Run:
```bash
wc -l /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/design/bits-header.css
```
Expected: ~130 lines

---

## Phase 2: Component Styles

### Task 2.1: Create Post List Styles

**Files:**
- Create: `themes/nodeseek/design/bits-post-list.css`

**Step 1: Create bits-post-list.css**

Create file `themes/nodeseek/design/bits-post-list.css`:
```css
/* BitsMesh Theme - Post List Styles */

/* Post List Container */
.bits-post-list {
    margin: 0;
    padding: 0;
}

/* Post List Item */
.bits-post-list-item {
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    display: flex;
    padding: 8px;
    position: relative;
}

body.dark-layout .bits-post-list-item {
    border-bottom-color: rgba(0, 0, 0, 0.3);
}

@media screen and (max-width: 500px) {
    .bits-post-list-item {
        padding: 4px 8px;
    }
}

/* Avatar */
.bits-post-list-item .avatar-normal {
    border-radius: 15%;
    box-shadow: 0 0 3px rgba(0, 0, 0, 0.1);
    flex-shrink: 0;
    height: 40px;
    width: 40px;
}

/* Content Area */
.bits-post-list-content {
    flex: 1;
    margin-left: 10px;
    min-width: 0;
}

/* Post Title */
.bits-post-title {
    color: var(--bits-text);
    font-size: 14px;
    font-weight: 700;
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.bits-post-title:hover {
    color: var(--bits-link);
}

/* Post Info */
.bits-post-info {
    color: var(--bits-text-gray);
    font-size: 11px;
    margin-top: 2px;
}

.bits-post-info .info-item {
    margin-left: 4px;
    margin-right: 4px;
    white-space: nowrap;
}

.bits-post-info .info-item:first-child {
    margin-left: 0;
}

.bits-post-info .info-item * {
    vertical-align: middle;
}

/* Post Category Tag */
.bits-post-category {
    background: var(--bits-bg-sub);
    border: none;
    border-radius: 2px;
    bottom: 8px;
    box-shadow: 0 0 2px rgba(0, 0, 0, 0.5);
    color: var(--bits-text);
    font-size: 12px;
    height: 20px;
    line-height: 20px;
    padding: 0 4px;
    position: absolute;
    right: 13px;
}

/* Badges */
.bits-badge {
    border-radius: 3px;
    font-size: 12px;
    margin-right: 5px;
    padding: 0 4px;
    vertical-align: middle;
}

.bits-badge.pined {
    background-color: #303030;
    color: #fff;
}

.bits-badge.award {
    background-color: #fddea9;
    color: #774b00;
}

.bits-badge.hot {
    background-color: #ff4444;
    color: #fff;
}

/* List Controller (Sort/Filter) */
.bits-post-list-controler {
    align-items: center;
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

/* Sorter */
.bits-sorter {
    background-color: var(--bits-text-gray);
    border: 1px solid #737373;
    border-radius: 3px;
    display: inline-flex;
    margin: 0 8px;
    overflow: hidden;
}

.bits-sorter > a {
    background-color: var(--bits-bg-main);
    cursor: pointer;
    display: block;
    padding: 0 5px;
    color: var(--bits-text);
}

.bits-sorter > a.selected {
    background-color: var(--bits-text-gray);
    color: var(--bits-bg-main);
}
```

**Step 2: Verify file created**

Run:
```bash
wc -l /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/design/bits-post-list.css
```
Expected: ~130 lines

---

### Task 2.2: Create Post Content Styles

**Files:**
- Create: `themes/nodeseek/design/bits-post-content.css`

**Step 1: Create bits-post-content.css**

Create file `themes/nodeseek/design/bits-post-content.css`:
```css
/* BitsMesh Theme - Post Content Styles */

/* Post Wrapper */
.bits-post-wrapper .content-item {
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    padding: 8px;
    position: relative;
}

/* Post Header */
.bits-post .bits-post-title h1 {
    font-size: 18px;
    margin: 0 0 10px;
    padding: 0 8px;
}

.bits-post .bits-post-title h1 > * {
    vertical-align: middle;
}

/* Meta Info */
.bits-content-meta-info {
    display: flex;
    padding: 0 8px;
}

.bits-content-meta-info .avatar-wrapper {
    margin: 0 10px 4px 0;
}

.bits-content-meta-info .avatar-wrapper .avatar-normal {
    height: 40px;
    width: 40px;
}

.bits-content-meta-info .author-info,
.bits-content-meta-info .content-info {
    font-size: 11px;
}

.bits-content-meta-info .author-info > *,
.bits-content-meta-info .content-info > * {
    vertical-align: middle;
}

.bits-content-meta-info .content-info {
    color: #858585;
}

.bits-content-meta-info .author-name {
    font-size: 15px;
    font-weight: 700;
    color: var(--bits-text);
}

/* Role Tags */
.role-tag {
    border: 1px solid var(--bits-text);
    border-radius: 3px;
    font-size: 12px;
    margin-left: 4px;
    margin-right: 4px;
    padding: 0 3px;
}

.role-tag.role-admin {
    background-color: var(--bits-primary);
    border-color: var(--bits-primary);
    color: #fafafa;
}

.role-tag.role-mod {
    background-color: #3b82f6;
    border-color: #3b82f6;
    color: #fafafa;
}

.role-tag.role-vip {
    background-color: #f59e0b;
    border-color: #f59e0b;
    color: #fff;
}

.role-tag.role-banned {
    border-color: #d74c4c;
    color: #d74c4c;
}

/* Post Content Body */
.bits-post-content {
    color: var(--bits-text);
    font-size: 14px;
    line-height: 1.5;
    margin-bottom: 16px;
    margin-top: 14px;
    overflow: hidden;
    padding: 0 8px;
}

body.dark-layout .bits-post-content {
    color: var(--bits-text);
}

.bits-post-content p {
    margin: 10px 0;
}

.bits-post-content h2 {
    color: var(--bits-primary);
    font-size: 17px;
    line-height: 1.5;
    border-bottom: 1px solid #e9e9e9;
}

body.dark-layout .bits-post-content h2 {
    border-bottom-color: rgba(0, 0, 0, 0.3);
}

.bits-post-content h3 {
    font-size: 16px;
}

.bits-post-content a {
    color: #0dbc79;
}

/* Code Blocks */
.bits-post-content code,
.bits-post-content pre {
    background: #ffff9954;
    border: 1px solid #eec;
    border-radius: 2px;
    font-family: monospace;
    overflow: auto;
    padding: 4px 8px;
}

body.dark-layout .bits-post-content code,
body.dark-layout .bits-post-content pre {
    background: #2e2e04;
    border-color: #56560b;
}

.bits-post-content pre {
    box-sizing: border-box;
    margin: 1em 0;
    max-width: 100%;
}

.bits-post-content pre code {
    background-color: transparent;
    border: 0;
    padding: 0;
}

/* Blockquote */
.bits-post-content blockquote {
    background: rgba(0, 0, 0, 0.05);
    border-left: 4px solid rgba(0, 0, 0, 0.1);
    margin: 1em;
    min-width: 200px;
    overflow-y: auto;
    padding: 1ex 10px;
}

body.dark-layout .bits-post-content blockquote {
    background: rgba(255, 255, 255, 0.05);
    border-left-color: rgba(255, 255, 255, 0.1);
}

/* Tables */
.bits-post-content table {
    background: #f2f6fc;
    border: 1px solid #dcdcdc;
    border-collapse: unset;
    border-radius: 4px;
    border-spacing: 0;
    box-sizing: border-box;
    font-size: 13px;
    margin-bottom: 18px;
    max-width: 100%;
    overflow: hidden;
    text-align: center;
}

body.dark-layout .bits-post-content table {
    background: #323232;
    border-color: #1e1d1d;
}

.bits-post-content table thead th {
    background: #ebeef5;
    font-weight: 500;
}

body.dark-layout .bits-post-content table thead th {
    background: #333435;
}

.bits-post-content table td,
.bits-post-content table th {
    border-bottom: 1px solid #dcdcdc;
    border-right: 1px solid #dcdcdc;
    padding: 8px;
}

body.dark-layout .bits-post-content table td,
body.dark-layout .bits-post-content table th {
    border-color: #1e1d1d;
}

/* Lists */
.bits-post-content ol,
.bits-post-content ul {
    margin: 1em 0 1em 2em;
}

.bits-post-content ol li {
    list-style: decimal !important;
}

.bits-post-content ul li {
    list-style: none !important;
    position: relative;
}

.bits-post-content ul li::before {
    content: "\2022";
    left: -1em;
    position: absolute;
}

/* Images */
.bits-post-content img {
    box-shadow: 0 0 4px rgba(0, 0, 0, 0.4);
    cursor: pointer;
    margin: 1rem 2.5%;
    max-width: 95%;
}

.bits-post-content img.sticker {
    box-shadow: none;
    margin: 0;
    max-width: 90px;
    vertical-align: middle;
}
```

**Step 2: Verify file created**

Run:
```bash
wc -l /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/design/bits-post-content.css
```
Expected: ~200 lines

---

### Task 2.3: Create Comment Styles

**Files:**
- Create: `themes/nodeseek/design/bits-comments.css`

**Step 1: Create bits-comments.css**

Create file `themes/nodeseek/design/bits-comments.css`:
```css
/* BitsMesh Theme - Comment Styles */

/* Comment Item */
.bits-comment-item {
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    padding: 12px 8px;
    position: relative;
}

body.dark-layout .bits-comment-item {
    border-bottom-color: rgba(0, 0, 0, 0.3);
}

/* Comment Header */
.bits-comment-header {
    display: flex;
    align-items: flex-start;
}

.bits-comment-header .avatar-wrapper {
    margin-right: 10px;
}

.bits-comment-header .avatar-normal {
    height: 40px;
    width: 40px;
}

/* Comment Meta */
.bits-comment-meta {
    flex: 1;
}

.bits-comment-meta .author-name {
    font-size: 14px;
    font-weight: 700;
    color: var(--bits-text);
}

.bits-comment-meta .comment-date {
    color: var(--bits-text-gray);
    font-size: 11px;
    margin-left: 10px;
}

/* Comment Body */
.bits-comment-body {
    margin-top: 8px;
    padding-left: 50px;
}

/* Comment Menu */
.bits-comment-menu {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    margin-top: 8px;
    padding-left: 50px;
}

.bits-comment-menu .menu-item {
    color: var(--bits-text-gray);
    cursor: pointer;
    margin-right: 15px;
    font-size: 12px;
}

.bits-comment-menu .menu-item:hover {
    color: var(--bits-text);
}

body.dark-layout .bits-comment-menu .menu-item:hover {
    color: #ccc;
}

.bits-comment-menu .menu-item.clicked {
    color: #e70606;
}

/* Floor Link */
.bits-floor-link {
    position: absolute;
    right: 8px;
    top: 12px;
    color: var(--bits-text-gray);
    font-size: 12px;
}

/* Pinned Comment Badge */
.bits-pined-comment-badge {
    background-color: var(--bits-primary);
    border-radius: 50%;
    box-shadow: 2px 2px 3px rgba(24, 68, 20, 0.45);
    color: #fff;
    height: 16px;
    padding: 3px;
    vertical-align: -5px;
    width: 16px;
}

/* Is Poster Badge */
.bits-is-poster {
    border: 1px solid #00eaff;
    border-radius: 3px;
    color: #00eaff;
    font-size: 11px;
    padding: 0 3px;
    margin-left: 5px;
}

/* Signature */
.bits-signature {
    border-top: 1px dashed #e2e2e2;
    color: #787878;
    margin-top: 10px;
    max-height: 44px;
    overflow: hidden;
    padding-top: 5px;
}

body.dark-layout .bits-signature {
    border-top-color: rgba(0, 0, 0, 0.3);
}

.bits-signature > * {
    opacity: 0.5;
    transition: opacity 0.2s;
}

.bits-signature:hover > * {
    opacity: 0.7;
}

.bits-signature a {
    color: #55b786;
}

.bits-signature a:hover {
    color: #23dc7f;
}
```

**Step 2: Verify file created**

Run:
```bash
wc -l /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/design/bits-comments.css
```
Expected: ~130 lines

---

### Task 2.4: Create Main Stylesheet

**Files:**
- Create: `themes/nodeseek/design/custom.css`

**Step 1: Create custom.css that imports all other stylesheets**

Create file `themes/nodeseek/design/custom.css`:
```css
/* BitsMesh Theme - Main Stylesheet */
/* Version: 1.0.0 */

/* Import all component stylesheets */
@import url('variables.css');
@import url('bits-base.css');
@import url('bits-layout.css');
@import url('bits-header.css');
@import url('bits-post-list.css');
@import url('bits-post-content.css');
@import url('bits-comments.css');

/* Additional utilities and overrides */

/* Button Styles */
.bits-btn {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-color: var(--bits-primary);
    border: 1px solid rgba(27, 31, 35, 0.15);
    border-radius: 6px;
    box-shadow: 0 1px 0 rgba(27, 31, 35, 0.1);
    box-sizing: border-box;
    color: #fff;
    cursor: pointer;
    display: inline-block;
    font-size: 14px;
    font-weight: 600;
    height: 27px;
    line-height: 25px;
    padding: 0 1rem;
    text-align: center;
    text-decoration: none;
    transition: background-color 0.2s;
    vertical-align: middle;
    white-space: nowrap;
}

.bits-btn:hover {
    background-color: var(--bits-secondary);
    color: #fff;
}

body.dark-layout .bits-btn {
    background-color: #158736;
}

body.dark-layout .bits-btn:hover {
    background-color: #23af4a;
}

/* Pager Styles */
.bits-pager {
    margin-top: 10px;
    text-align: center;
}

.bits-pager a,
.bits-pager span {
    border: 1px solid rgba(0, 0, 0, 0.01);
    margin: 0 2px;
}

.bits-pager .pager-pos {
    border-radius: 3px;
    box-sizing: border-box;
    color: var(--bits-text-gray);
    display: inline-block;
    font-family: monospace;
    font-size: 16px;
    height: 18px;
    line-height: 18px;
    min-width: 18px;
    text-align: center;
    vertical-align: middle;
}

.bits-pager .pager-pos:hover {
    background-color: rgba(0, 0, 0, 0.1);
}

body.dark-layout .bits-pager .pager-pos:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.bits-pager .pager-cur {
    background-color: var(--bits-bg-sub) !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
    cursor: not-allowed;
}

/* Image Lightbox */
.bits-image-box {
    background-color: rgba(0, 0, 0, 0.8);
    box-sizing: border-box;
    height: 100%;
    left: 50%;
    line-height: 100vh;
    overflow: auto;
    padding: 0 10%;
    position: fixed;
    top: 50%;
    transform: translateY(-50%) translateX(-50%);
    width: 100%;
    z-index: 9999;
}

@media screen and (max-width: 500px) {
    .bits-image-box {
        padding: 0;
    }
}

.bits-image-box img {
    margin: auto;
    max-width: 100%;
    vertical-align: middle;
}

/* Fast Navigation Buttons */
#bits-fast-nav {
    bottom: 40px;
    position: fixed;
    right: calc(50% - 590px);
    z-index: 99;
}

@media screen and (max-width: 500px) {
    #bits-fast-nav {
        right: 30px;
    }
}

.bits-nav-btn {
    align-items: center;
    background-color: var(--bits-bg-main);
    border: 1px solid var(--bits-glass);
    border-radius: 20%;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
    box-sizing: border-box;
    cursor: pointer;
    display: flex;
    height: 30px;
    justify-content: center;
    margin-bottom: 10px;
    width: 30px;
}

.bits-nav-btn:hover {
    color: var(--bits-link-hover);
}

/* Skeleton Loading */
.bits-skeleton {
    animation: bits-loading 2s ease infinite;
}

@keyframes bits-loading {
    0% {
        background: #aaa;
        background-image: linear-gradient(100deg, #eee 40%, #fff 50%, #eee 60%);
        background-position: 100% 50%;
        background-size: 300% 100%;
    }
    100% {
        background: #aaa;
        background-image: linear-gradient(100deg, #eee 40%, #fff 50%, #eee 60%);
        background-position: 0 50%;
        background-size: 300% 100%;
    }
}

body.dark-layout .bits-skeleton {
    animation: bits-loading-dark 2s ease infinite;
}

@keyframes bits-loading-dark {
    0% {
        background-image: linear-gradient(100deg, #3f3f3f 40%, #2f2f2f 50%, #3f3f3f 60%);
        background-position: 100% 50%;
        background-size: 300% 100%;
    }
    100% {
        background-image: linear-gradient(100deg, #3f3f3f 40%, #2f2f2f 50%, #3f3f3f 60%);
        background-position: 0 50%;
        background-size: 300% 100%;
    }
}

/* Background Patterns */
body.bits-bg-grid {
    background-image: linear-gradient(#d4d4d4 1px, transparent 0),
        linear-gradient(90deg, #d4d4d4 1px, transparent 0);
    background-size: 32px 32px;
}

body.dark-layout.bits-bg-grid {
    background-image: linear-gradient(var(--bits-text-fade) 1px, transparent 1px),
        linear-gradient(to right, var(--bits-text-fade) 1px, transparent 1px);
}
```

**Step 2: Verify file created**

Run:
```bash
wc -l /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/design/custom.css
```
Expected: ~200 lines

---

## Phase 3: JavaScript Implementation

### Task 3.1: Create Dark Mode Toggle

**Files:**
- Create: `themes/nodeseek/js/darkMode.js`

**Step 1: Create darkMode.js**

Create file `themes/nodeseek/js/darkMode.js`:
```javascript
/**
 * BitsMesh Theme - Dark Mode Toggle
 * Handles dark/light mode switching with localStorage persistence
 */

class DarkModeToggle {
    constructor() {
        this.storageKey = 'bits-theme-mode';
        this.darkClass = 'dark-layout';
        this.toggleSelector = '.bits-dark-toggle';
    }

    /**
     * Initialize dark mode based on saved preference
     */
    init() {
        const saved = localStorage.getItem(this.storageKey);
        if (saved === 'dark') {
            document.body.classList.add(this.darkClass);
            this.updateIcon(true);
        }
        this.bindToggleButton();
    }

    /**
     * Bind click event to toggle button
     */
    bindToggleButton() {
        const toggleBtn = document.querySelector(this.toggleSelector);
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => this.toggle());
        }
    }

    /**
     * Toggle between dark and light mode
     */
    toggle() {
        const isDark = document.body.classList.toggle(this.darkClass);
        localStorage.setItem(this.storageKey, isDark ? 'dark' : 'light');
        this.updateIcon(isDark);
    }

    /**
     * Update the toggle button icon
     * @param {boolean} isDark - Current dark mode state
     */
    updateIcon(isDark) {
        const toggleBtn = document.querySelector(this.toggleSelector);
        if (toggleBtn) {
            toggleBtn.textContent = isDark ? '☀️' : '🌙';
            toggleBtn.title = isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode';
        }
    }

    /**
     * Get current mode
     * @returns {string} 'dark' or 'light'
     */
    getMode() {
        return document.body.classList.contains(this.darkClass) ? 'dark' : 'light';
    }

    /**
     * Set mode programmatically
     * @param {string} mode - 'dark' or 'light'
     */
    setMode(mode) {
        if (mode === 'dark') {
            document.body.classList.add(this.darkClass);
        } else {
            document.body.classList.remove(this.darkClass);
        }
        localStorage.setItem(this.storageKey, mode);
        this.updateIcon(mode === 'dark');
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    window.bitsTheme = window.bitsTheme || {};
    window.bitsTheme.darkMode = new DarkModeToggle();
    window.bitsTheme.darkMode.init();
});
```

**Step 2: Verify file created**

Run:
```bash
wc -l /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/js/darkMode.js
```
Expected: ~80 lines

---

### Task 3.2: Create Cache Manager

**Files:**
- Create: `themes/nodeseek/js/cache.js`

**Step 1: Create cache.js**

Create file `themes/nodeseek/js/cache.js`:
```javascript
/**
 * BitsMesh Theme - Client-side Cache Manager
 * Handles caching of API responses in localStorage
 */

class DataCache {
    constructor(options = {}) {
        this.storage = localStorage;
        this.prefix = options.prefix || 'bits-cache-';
        this.defaultTTL = options.ttl || 5 * 60 * 1000; // 5 minutes default
    }

    /**
     * Generate cache key with prefix
     * @param {string} key - Original key
     * @returns {string} Prefixed key
     */
    _getKey(key) {
        return this.prefix + key;
    }

    /**
     * Set data in cache
     * @param {string} key - Cache key
     * @param {*} data - Data to cache
     * @param {number} ttl - Time to live in milliseconds (optional)
     */
    set(key, data, ttl = this.defaultTTL) {
        const cacheData = {
            data: data,
            timestamp: Date.now(),
            ttl: ttl
        };
        try {
            this.storage.setItem(this._getKey(key), JSON.stringify(cacheData));
        } catch (e) {
            // Handle quota exceeded
            if (e.name === 'QuotaExceededError') {
                this.clearExpired();
                try {
                    this.storage.setItem(this._getKey(key), JSON.stringify(cacheData));
                } catch (e2) {
                    console.warn('Cache storage full, unable to cache:', key);
                }
            }
        }
    }

    /**
     * Get data from cache
     * @param {string} key - Cache key
     * @returns {*} Cached data or null if expired/not found
     */
    get(key) {
        const cached = this.storage.getItem(this._getKey(key));
        if (!cached) return null;

        try {
            const { data, timestamp, ttl } = JSON.parse(cached);
            if (Date.now() - timestamp > ttl) {
                this.remove(key);
                return null;
            }
            return data;
        } catch (e) {
            this.remove(key);
            return null;
        }
    }

    /**
     * Check if key exists and is valid
     * @param {string} key - Cache key
     * @returns {boolean}
     */
    has(key) {
        return this.get(key) !== null;
    }

    /**
     * Remove item from cache
     * @param {string} key - Cache key
     */
    remove(key) {
        this.storage.removeItem(this._getKey(key));
    }

    /**
     * Clear all expired items
     */
    clearExpired() {
        const keys = Object.keys(this.storage);
        const now = Date.now();

        keys.forEach(key => {
            if (key.startsWith(this.prefix)) {
                try {
                    const { timestamp, ttl } = JSON.parse(this.storage.getItem(key));
                    if (now - timestamp > ttl) {
                        this.storage.removeItem(key);
                    }
                } catch (e) {
                    this.storage.removeItem(key);
                }
            }
        });
    }

    /**
     * Clear all cached data
     */
    clear() {
        const keys = Object.keys(this.storage);
        keys.forEach(key => {
            if (key.startsWith(this.prefix)) {
                this.storage.removeItem(key);
            }
        });
    }

    /**
     * Get or fetch data with caching
     * @param {string} key - Cache key
     * @param {Function} fetcher - Async function to fetch data if not cached
     * @param {number} ttl - Time to live (optional)
     * @returns {Promise<*>}
     */
    async getOrFetch(key, fetcher, ttl = this.defaultTTL) {
        const cached = this.get(key);
        if (cached !== null) {
            return cached;
        }

        const data = await fetcher();
        this.set(key, data, ttl);
        return data;
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    window.bitsTheme = window.bitsTheme || {};
    window.bitsTheme.cache = new DataCache();
});
```

**Step 2: Verify file created**

Run:
```bash
wc -l /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/js/cache.js
```
Expected: ~120 lines

---

### Task 3.3: Create Concurrency Controller

**Files:**
- Create: `themes/nodeseek/js/concurrency.js`

**Step 1: Create concurrency.js**

Create file `themes/nodeseek/js/concurrency.js`:
```javascript
/**
 * BitsMesh Theme - Concurrency Controller
 * Handles request queuing, optimistic updates, and debouncing
 */

class RequestQueue {
    constructor() {
        this.queue = [];
        this.processing = false;
        this.maxConcurrent = 3;
        this.activeRequests = 0;
    }

    /**
     * Add request to queue
     * @param {Function} request - Async function to execute
     * @returns {Promise}
     */
    async enqueue(request) {
        return new Promise((resolve, reject) => {
            this.queue.push({ request, resolve, reject });
            this.process();
        });
    }

    /**
     * Process queue
     */
    async process() {
        if (this.activeRequests >= this.maxConcurrent || this.queue.length === 0) {
            return;
        }

        const { request, resolve, reject } = this.queue.shift();
        this.activeRequests++;

        try {
            const result = await request();
            resolve(result);
        } catch (error) {
            reject(error);
        } finally {
            this.activeRequests--;
            this.process();
        }
    }

    /**
     * Optimistic update with version control
     * @param {string} endpoint - API endpoint
     * @param {Object} data - Data to send
     * @param {number} version - Current version number
     * @returns {Promise<Object>}
     */
    async optimisticUpdate(endpoint, data, version) {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ ...data, _version: version })
        });

        if (response.status === 409) {
            // Version conflict
            return {
                conflict: true,
                newData: await response.json()
            };
        }

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        return {
            success: true,
            data: await response.json()
        };
    }
}

/**
 * Debounce function
 * @param {Function} fn - Function to debounce
 * @param {number} delay - Delay in milliseconds
 * @returns {Function}
 */
function debounce(fn, delay = 300) {
    let timer = null;
    return function(...args) {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, args), delay);
    };
}

/**
 * Throttle function
 * @param {Function} fn - Function to throttle
 * @param {number} limit - Minimum time between calls in milliseconds
 * @returns {Function}
 */
function throttle(fn, limit = 300) {
    let inThrottle = false;
    return function(...args) {
        if (!inThrottle) {
            fn.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

/**
 * Retry wrapper for async functions
 * @param {Function} fn - Async function to retry
 * @param {number} maxRetries - Maximum number of retries
 * @param {number} delay - Delay between retries in milliseconds
 * @returns {Promise}
 */
async function retry(fn, maxRetries = 3, delay = 1000) {
    let lastError;
    for (let i = 0; i < maxRetries; i++) {
        try {
            return await fn();
        } catch (error) {
            lastError = error;
            if (i < maxRetries - 1) {
                await new Promise(resolve => setTimeout(resolve, delay * (i + 1)));
            }
        }
    }
    throw lastError;
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    window.bitsTheme = window.bitsTheme || {};
    window.bitsTheme.requestQueue = new RequestQueue();
    window.bitsTheme.debounce = debounce;
    window.bitsTheme.throttle = throttle;
    window.bitsTheme.retry = retry;
});
```

**Step 2: Verify file created**

Run:
```bash
wc -l /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/js/concurrency.js
```
Expected: ~130 lines

---

### Task 3.4: Create Main Theme Script

**Files:**
- Create: `themes/nodeseek/js/theme.js`

**Step 1: Create theme.js**

Create file `themes/nodeseek/js/theme.js`:
```javascript
/**
 * BitsMesh Theme - Main Entry Point
 * Initializes all theme components
 */

(function() {
    'use strict';

    // Initialize theme namespace
    window.bitsTheme = window.bitsTheme || {};

    /**
     * Image Lightbox
     */
    class ImageBox {
        constructor() {
            this.boxClass = 'bits-image-box';
        }

        init() {
            document.addEventListener('click', (e) => {
                // Open lightbox on image click
                if (e.target.matches('.bits-post-content img:not(.sticker)')) {
                    this.open(e.target.src);
                }
                // Close lightbox on click
                if (e.target.matches('.' + this.boxClass) ||
                    e.target.matches('.' + this.boxClass + ' img')) {
                    this.close();
                }
            });

            // Close on escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.close();
                }
            });
        }

        open(src) {
            const box = document.createElement('div');
            box.className = this.boxClass;
            box.innerHTML = `<img src="${src}" alt="Preview">`;
            document.body.appendChild(box);
            document.body.style.overflow = 'hidden';
        }

        close() {
            const box = document.querySelector('.' + this.boxClass);
            if (box) {
                box.remove();
                document.body.style.overflow = '';
            }
        }
    }

    /**
     * Back to Top Button
     */
    class BackToTop {
        constructor() {
            this.btnSelector = '#bits-back-to-top';
            this.showThreshold = 300;
        }

        init() {
            const btn = document.querySelector(this.btnSelector);
            if (!btn) return;

            // Show/hide based on scroll
            window.addEventListener('scroll', bitsTheme.throttle(() => {
                if (window.scrollY > this.showThreshold) {
                    btn.style.display = 'flex';
                } else {
                    btn.style.display = 'none';
                }
            }, 100));

            // Scroll to top on click
            btn.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
    }

    /**
     * Mobile Navigation
     */
    class MobileNav {
        constructor() {
            this.hamburgerSelector = '.bits-hamburger';
            this.navSelector = '#bits-mobile-nav-panel';
            this.openClass = 'is-open';
        }

        init() {
            const hamburger = document.querySelector(this.hamburgerSelector);
            const nav = document.querySelector(this.navSelector);

            if (!hamburger || !nav) return;

            hamburger.addEventListener('click', () => {
                nav.classList.toggle(this.openClass);
                hamburger.classList.toggle('is-active');
            });

            // Close on outside click
            document.addEventListener('click', (e) => {
                if (!nav.contains(e.target) && !hamburger.contains(e.target)) {
                    nav.classList.remove(this.openClass);
                    hamburger.classList.remove('is-active');
                }
            });
        }
    }

    /**
     * Initialize all components
     */
    function initTheme() {
        // Image lightbox
        bitsTheme.imageBox = new ImageBox();
        bitsTheme.imageBox.init();

        // Back to top
        bitsTheme.backToTop = new BackToTop();
        bitsTheme.backToTop.init();

        // Mobile navigation
        bitsTheme.mobileNav = new MobileNav();
        bitsTheme.mobileNav.init();

        // Add background pattern class
        document.body.classList.add('bits-bg-grid');

        console.log('BitsMesh Theme initialized');
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTheme);
    } else {
        initTheme();
    }
})();
```

**Step 2: Verify file created**

Run:
```bash
wc -l /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/js/theme.js
```
Expected: ~130 lines

---

## Phase 4: Vanilla Templates

### Task 4.1: Create Master Template

**Files:**
- Create: `themes/nodeseek/views/default.master.tpl`

**Step 1: Create default.master.tpl**

Create file `themes/nodeseek/views/default.master.tpl`:
```smarty
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

    <header class="bits-header">
        <div id="bits-head" class="bits-container">
            {include file="partials/header.tpl"}
        </div>
    </header>

    <!-- Left Panel - Categories (Large screens only) -->
    <aside id="bits-left-panel">
        <nav class="bits-category-list">
            {categories_module}
        </nav>
    </aside>

    <section id="bits-frame">
        <div id="bits-body" class="bits-container">
            <!-- Main Content -->
            <main id="bits-body-left">
                {if !$isHomepage}
                    <nav class="bits-breadcrumbs">
                        {breadcrumbs}
                    </nav>
                {/if}

                <div id="bits-content">
                    {asset name="Content"}
                </div>
            </main>

            <!-- Right Panel -->
            <aside id="bits-right-panel">
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

    <footer class="bits-footer">
        {include file="partials/footer.tpl"}
    </footer>

    <!-- Fast Navigation -->
    <div id="bits-fast-nav">
        <button id="bits-back-to-top" class="bits-nav-btn" style="display:none" title="Back to Top">
            ↑
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
```

**Step 2: Verify file created**

Run:
```bash
wc -l /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/views/default.master.tpl
```
Expected: ~80 lines

---

### Task 4.2: Create Header Partial

**Files:**
- Create: `themes/nodeseek/views/partials/header.tpl`

**Step 1: Create header.tpl**

Create file `themes/nodeseek/views/partials/header.tpl`:
```smarty
<!-- Site Logo/Title -->
<div class="bits-site-title">
    <a href="{link path="/"}">{$Title}</a>
</div>

<!-- Main Navigation -->
<nav class="bits-nav-menu">
    <ul>
        <li class="{if $isHomepage}current-category{/if}">
            <a href="{link path="/"}">Home</a>
        </li>
        <li class="{if inSection('CategoryList')}current-category{/if}">
            <a href="{link path="/categories"}">Categories</a>
        </li>
        <li class="{if inSection('DiscussionList')}current-category{/if}">
            <a href="{link path="/discussions"}">Discussions</a>
        </li>
        {if $User.SignedIn}
            <li>
                <a href="{link path="/activity"}">Activity</a>
            </li>
        {/if}
    </ul>
</nav>

<!-- Search Box -->
<div class="bits-search-box">
    <input type="text" id="bits-search-input" placeholder="Search..." />
</div>

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
<div class="bits-dark-toggle" title="Toggle Dark Mode">
    🌙
</div>

<!-- Mobile Hamburger -->
<button class="bits-hamburger" aria-label="Menu">
    <span></span>
    <span></span>
    <span></span>
</button>
```

**Step 2: Verify file created**

Run:
```bash
wc -l /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/views/partials/header.tpl
```
Expected: ~55 lines

---

### Task 4.3: Create Footer Partial

**Files:**
- Create: `themes/nodeseek/views/partials/footer.tpl`

**Step 1: Create footer.tpl**

Create file `themes/nodeseek/views/partials/footer.tpl`:
```smarty
<div class="bits-container">
    <div class="bits-footer-content">
        <div class="bits-footer-links">
            <a href="{link path="/"}">Home</a>
            <a href="{link path="/categories"}">Categories</a>
            {if $User.SignedIn}
                <a href="{link path="/profile"}">Profile</a>
            {/if}
        </div>

        <div class="bits-footer-copyright">
            <p>
                Powered by <a href="https://vanillaforums.com" target="_blank">Vanilla Forums</a>
                &middot;
                BitsMesh Theme &copy; {date('Y')}
            </p>
        </div>
    </div>
</div>
```

**Step 2: Verify file created**

Run:
```bash
wc -l /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/views/partials/footer.tpl
```
Expected: ~20 lines

---

## Phase 5: Theme Configuration

### Task 5.1: Create Theme Configuration

**Files:**
- Create: `themes/nodeseek/settings/configuration.php`

**Step 1: Create configuration.php**

Create file `themes/nodeseek/settings/configuration.php`:
```php
<?php
/**
 * BitsMesh Theme Configuration
 *
 * This file defines the theme options available in the dashboard.
 */

// Theme color options
$Configuration['Garden']['ThemeOptions']['Options']['PrimaryColor'] = [
    'Type' => 'color',
    'Default' => '#2ea44f',
    'Description' => 'Primary theme color (buttons, links, accents)'
];

$Configuration['Garden']['ThemeOptions']['Options']['SecondaryColor'] = [
    'Type' => 'color',
    'Default' => '#45ca6b',
    'Description' => 'Secondary theme color (hover states)'
];

$Configuration['Garden']['ThemeOptions']['Options']['TextColor'] = [
    'Type' => 'color',
    'Default' => '#333333',
    'Description' => 'Main text color'
];

$Configuration['Garden']['ThemeOptions']['Options']['LinkColor'] = [
    'Type' => 'color',
    'Default' => '#555555',
    'Description' => 'Link color'
];

$Configuration['Garden']['ThemeOptions']['Options']['BgMainColor'] = [
    'Type' => 'color',
    'Default' => '#ffffff',
    'Description' => 'Main background color'
];

$Configuration['Garden']['ThemeOptions']['Options']['BgSubColor'] = [
    'Type' => 'color',
    'Default' => '#fbfbfb',
    'Description' => 'Secondary background color'
];

// Dark mode defaults
$Configuration['Garden']['ThemeOptions']['Options']['DarkTextColor'] = [
    'Type' => 'color',
    'Default' => '#aaaaaa',
    'Description' => 'Dark mode text color'
];

$Configuration['Garden']['ThemeOptions']['Options']['DarkBgMainColor'] = [
    'Type' => 'color',
    'Default' => '#272727',
    'Description' => 'Dark mode main background'
];

$Configuration['Garden']['ThemeOptions']['Options']['DarkBgSubColor'] = [
    'Type' => 'color',
    'Default' => '#3b3b3b',
    'Description' => 'Dark mode secondary background'
];
```

**Step 2: Verify file created**

Run:
```bash
wc -l /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/settings/configuration.php
```
Expected: ~60 lines

---

## Phase 6: Testing Setup

### Task 6.1: Create Playwright Configuration

**Files:**
- Create: `themes/nodeseek/tests/playwright.config.ts`

**Step 1: Create playwright.config.ts**

Create file `themes/nodeseek/tests/playwright.config.ts`:
```typescript
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: '.',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: 'html',

  use: {
    baseURL: 'http://localhost:8357',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    {
      name: 'mobile',
      use: { ...devices['iPhone 12'] },
    },
  ],

  expect: {
    toHaveScreenshot: {
      maxDiffPixels: 100,
    },
  },
});
```

**Step 2: Create package.json for tests**

Create file `themes/nodeseek/tests/package.json`:
```json
{
  "name": "bitsmesh-theme-tests",
  "version": "1.0.0",
  "scripts": {
    "test": "playwright test",
    "test:e2e": "playwright test e2e/",
    "test:visual": "playwright test visual/",
    "test:update": "playwright test --update-snapshots"
  },
  "devDependencies": {
    "@playwright/test": "^1.40.0"
  }
}
```

**Step 3: Verify files created**

Run:
```bash
ls -la /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/tests/
```
Expected: playwright.config.ts and package.json

---

### Task 6.2: Create E2E Tests

**Files:**
- Create: `themes/nodeseek/tests/e2e/homepage.spec.ts`
- Create: `themes/nodeseek/tests/e2e/dark-mode.spec.ts`

**Step 1: Create homepage.spec.ts**

Create file `themes/nodeseek/tests/e2e/homepage.spec.ts`:
```typescript
import { test, expect } from '@playwright/test';

test.describe('Homepage', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
  });

  test('should display header with site title', async ({ page }) => {
    await expect(page.locator('.bits-site-title')).toBeVisible();
  });

  test('should display navigation menu', async ({ page }) => {
    await expect(page.locator('.bits-nav-menu')).toBeVisible();
  });

  test('should display main content area', async ({ page }) => {
    await expect(page.locator('#bits-body-left')).toBeVisible();
  });

  test('should display right panel on desktop', async ({ page }) => {
    await expect(page.locator('#bits-right-panel')).toBeVisible();
  });

  test('should have dark mode toggle', async ({ page }) => {
    await expect(page.locator('.bits-dark-toggle')).toBeVisible();
  });

  test('should have search box', async ({ page }) => {
    await expect(page.locator('.bits-search-box')).toBeVisible();
  });
});

test.describe('Homepage - Mobile', () => {
  test.use({ viewport: { width: 375, height: 667 } });

  test('should hide right panel on mobile', async ({ page }) => {
    await page.goto('/');
    await expect(page.locator('#bits-right-panel')).not.toBeVisible();
  });

  test('should show hamburger menu on mobile', async ({ page }) => {
    await page.goto('/');
    await expect(page.locator('.bits-hamburger')).toBeVisible();
  });
});
```

**Step 2: Create dark-mode.spec.ts**

Create file `themes/nodeseek/tests/e2e/dark-mode.spec.ts`:
```typescript
import { test, expect } from '@playwright/test';

test.describe('Dark Mode', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/');
    // Clear localStorage before each test
    await page.evaluate(() => localStorage.clear());
  });

  test('should toggle dark mode on click', async ({ page }) => {
    const toggle = page.locator('.bits-dark-toggle');

    // Initially should be light mode
    await expect(page.locator('body')).not.toHaveClass(/dark-layout/);

    // Click to enable dark mode
    await toggle.click();
    await expect(page.locator('body')).toHaveClass(/dark-layout/);

    // Click to disable dark mode
    await toggle.click();
    await expect(page.locator('body')).not.toHaveClass(/dark-layout/);
  });

  test('should persist dark mode preference', async ({ page }) => {
    // Enable dark mode
    await page.locator('.bits-dark-toggle').click();
    await expect(page.locator('body')).toHaveClass(/dark-layout/);

    // Reload page
    await page.reload();

    // Should still be dark mode
    await expect(page.locator('body')).toHaveClass(/dark-layout/);
  });

  test('should update toggle icon', async ({ page }) => {
    const toggle = page.locator('.bits-dark-toggle');

    // Light mode should show moon
    await expect(toggle).toContainText('🌙');

    // Dark mode should show sun
    await toggle.click();
    await expect(toggle).toContainText('☀️');
  });
});
```

**Step 3: Verify files created**

Run:
```bash
ls -la /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/tests/e2e/
```
Expected: homepage.spec.ts and dark-mode.spec.ts

---

### Task 6.3: Create Visual Regression Tests

**Files:**
- Create: `themes/nodeseek/tests/visual/visual-regression.spec.ts`

**Step 1: Create visual-regression.spec.ts**

Create file `themes/nodeseek/tests/visual/visual-regression.spec.ts`:
```typescript
import { test, expect } from '@playwright/test';

test.describe('Visual Regression', () => {
  test('homepage - light mode', async ({ page }) => {
    await page.goto('/');
    await page.evaluate(() => localStorage.setItem('bits-theme-mode', 'light'));
    await page.reload();
    await page.waitForLoadState('networkidle');

    await expect(page).toHaveScreenshot('homepage-light.png', {
      fullPage: true,
    });
  });

  test('homepage - dark mode', async ({ page }) => {
    await page.goto('/');
    await page.evaluate(() => localStorage.setItem('bits-theme-mode', 'dark'));
    await page.reload();
    await page.waitForLoadState('networkidle');

    await expect(page).toHaveScreenshot('homepage-dark.png', {
      fullPage: true,
    });
  });

  test('header component', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('networkidle');

    await expect(page.locator('header.bits-header')).toHaveScreenshot('header.png');
  });

  test('right panel component', async ({ page }) => {
    await page.goto('/');
    await page.waitForLoadState('networkidle');

    await expect(page.locator('#bits-right-panel')).toHaveScreenshot('right-panel.png');
  });
});

test.describe('Visual Regression - Mobile', () => {
  test.use({ viewport: { width: 375, height: 667 } });

  test('homepage mobile - light mode', async ({ page }) => {
    await page.goto('/');
    await page.evaluate(() => localStorage.setItem('bits-theme-mode', 'light'));
    await page.reload();
    await page.waitForLoadState('networkidle');

    await expect(page).toHaveScreenshot('homepage-mobile-light.png', {
      fullPage: true,
    });
  });

  test('homepage mobile - dark mode', async ({ page }) => {
    await page.goto('/');
    await page.evaluate(() => localStorage.setItem('bits-theme-mode', 'dark'));
    await page.reload();
    await page.waitForLoadState('networkidle');

    await expect(page).toHaveScreenshot('homepage-mobile-dark.png', {
      fullPage: true,
    });
  });
});
```

**Step 2: Verify file created**

Run:
```bash
wc -l /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/tests/visual/visual-regression.spec.ts
```
Expected: ~65 lines

---

## Phase 7: Integration & Verification

### Task 7.1: Verify Theme Structure

**Step 1: List all theme files**

Run:
```bash
find /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek -type f | head -30
```
Expected: All created files listed

**Step 2: Verify addon.json is valid JSON**

Run:
```bash
cat /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/addon.json | python3 -m json.tool > /dev/null && echo "Valid JSON"
```
Expected: "Valid JSON"

---

### Task 7.2: Install Test Dependencies

**Step 1: Install Playwright**

Run:
```bash
cd /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/tests && npm install
```
Expected: Dependencies installed

**Step 2: Install Playwright browsers**

Run:
```bash
cd /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/tests && npx playwright install chromium
```
Expected: Chromium installed

---

### Task 7.3: Run Initial Tests

**Step 1: Run E2E tests**

Run:
```bash
cd /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/tests && npx playwright test e2e/ --reporter=list
```
Expected: Tests run (may fail initially if Vanilla not configured)

**Step 2: Generate visual baseline screenshots**

Run:
```bash
cd /Users/kilmu/Dev/WebDev/vanilla/themes/nodeseek/tests && npx playwright test visual/ --update-snapshots
```
Expected: Baseline screenshots created

---

### Task 7.4: Enable Theme in Vanilla

**Step 1: Access Vanilla admin panel**

Open browser: http://localhost:8357/dashboard/settings/themes

**Step 2: Activate BitsMesh theme**

- Find "BitsMesh Theme" in the theme list
- Click "Apply" to activate

**Step 3: Verify theme is active**

Open browser: http://localhost:8357/
Expected: BitsMesh theme styles visible

---

## Summary

**Total Tasks:** 22 tasks across 7 phases

**Files Created:**
- `themes/nodeseek/addon.json`
- `themes/nodeseek/design/variables.css`
- `themes/nodeseek/design/bits-base.css`
- `themes/nodeseek/design/bits-layout.css`
- `themes/nodeseek/design/bits-header.css`
- `themes/nodeseek/design/bits-post-list.css`
- `themes/nodeseek/design/bits-post-content.css`
- `themes/nodeseek/design/bits-comments.css`
- `themes/nodeseek/design/custom.css`
- `themes/nodeseek/js/darkMode.js`
- `themes/nodeseek/js/cache.js`
- `themes/nodeseek/js/concurrency.js`
- `themes/nodeseek/js/theme.js`
- `themes/nodeseek/views/default.master.tpl`
- `themes/nodeseek/views/partials/header.tpl`
- `themes/nodeseek/views/partials/footer.tpl`
- `themes/nodeseek/settings/configuration.php`
- `themes/nodeseek/tests/playwright.config.ts`
- `themes/nodeseek/tests/package.json`
- `themes/nodeseek/tests/e2e/homepage.spec.ts`
- `themes/nodeseek/tests/e2e/dark-mode.spec.ts`
- `themes/nodeseek/tests/visual/visual-regression.spec.ts`

**Next Steps After Phase 7:**
1. Customize Vanilla core views for deeper integration
2. Add more page-specific styles (categories, profiles, search)
3. Implement mobile navigation panel
4. Add theme configuration UI in dashboard
