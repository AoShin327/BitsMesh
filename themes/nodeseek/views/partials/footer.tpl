<div class="bits-container">
    <div class="bits-footer-content">
        <nav class="bits-footer-links" aria-label="Footer navigation">
            <a href="{link path="/"}">Home</a>
            <a href="{link path="/categories"}">Categories</a>
            {if $User.SignedIn}
                <a href="{link path="/profile"}">Profile</a>
            {/if}
        </nav>

        <div class="bits-footer-copyright">
            <p>
                Powered by <a href="https://vanillaforums.com" target="_blank" rel="noopener noreferrer">Vanilla Forums</a>
                &middot;
                BitsMesh Theme &copy; {date('Y')}
            </p>
        </div>
    </div>
</div>
