/**
 * Credits System JavaScript
 *
 * Handles client-side interactions for:
 * - Check-in functionality
 * - Feed (give credits) functionality
 *
 * @author BitsMesh
 * @version 1.0.0
 */

(function($) {
    'use strict';

    // Wait for DOM ready
    $(function() {
        // Initialize feed buttons on discussion pages
        initFeedButtons();
    });

    /**
     * Initialize feed buttons on discussion pages.
     */
    function initFeedButtons() {
        // Add feed button to discussion author info (if not already exists)
        var $discussionMeta = $('.Discussion .bits-content-meta-info');
        if ($discussionMeta.length && !$discussionMeta.find('.credits-feed-btn').length) {
            var authorUserID = $discussionMeta.closest('[data-userid]').data('userid');
            if (authorUserID && authorUserID !== gdn.definition('UserID')) {
                var $feedBtn = createFeedButton(authorUserID, 0); // 0 = discussion itself
                $discussionMeta.find('.bits-author-info').append($feedBtn);
            }
        }

        // Delegate click handler for feed buttons
        $(document).on('click', '.credits-feed-btn', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var toUserID = $btn.data('userid');
            var discussionID = $btn.data('discussionid') || getDiscussionID();

            showFeedModal(toUserID, discussionID);
        });
    }

    /**
     * Create a feed button element.
     *
     * @param {number} userID Target user ID
     * @param {number} discussionID Discussion ID
     * @return {jQuery}
     */
    function createFeedButton(userID, discussionID) {
        return $('<button>')
            .addClass('credits-feed-btn')
            .attr({
                'data-userid': userID,
                'data-discussionid': discussionID,
                'title': gdn.definition('Credits.FeedTitle', '投喂鸡腿')
            })
            .html('<svg class="iconpark-icon"><use href="#chicken-leg"></use></svg><span>' + gdn.definition('Credits.Feed', '投喂') + '</span>');
    }

    /**
     * Show feed modal dialog.
     *
     * @param {number} toUserID Target user ID
     * @param {number} discussionID Discussion ID
     */
    function showFeedModal(toUserID, discussionID) {
        // Remove existing modal
        $('#credits-feed-modal').remove();

        var modalHtml = [
            '<div class="credits-feed-modal" id="credits-feed-modal">',
            '  <div class="credits-feed-modal-content">',
            '    <div class="credits-feed-modal-header">',
            '      <h3><svg class="iconpark-icon"><use href="#chicken-leg"></use></svg>' + gdn.definition('Credits.FeedTitle', '投喂鸡腿') + '</h3>',
            '      <button type="button" class="credits-feed-modal-close"><svg class="iconpark-icon"><use href="#close-small"></use></svg></button>',
            '    </div>',
            '    <div class="credits-feed-amounts">',
            '      <button type="button" class="credits-feed-amount-btn selected" data-amount="1">1</button>',
            '      <button type="button" class="credits-feed-amount-btn" data-amount="5">5</button>',
            '      <button type="button" class="credits-feed-amount-btn" data-amount="10">10</button>',
            '    </div>',
            '    <button type="button" class="credits-feed-submit">' + gdn.definition('Credits.DoFeed', '确认投喂') + '</button>',
            '  </div>',
            '</div>'
        ].join('\n');

        var $modal = $(modalHtml).appendTo('body');

        // Show modal with animation
        setTimeout(function() {
            $modal.addClass('show');
        }, 10);

        // Close modal handlers
        $modal.on('click', function(e) {
            if (e.target === this) {
                closeFeedModal();
            }
        });

        $modal.find('.credits-feed-modal-close').on('click', closeFeedModal);

        // Amount selection
        $modal.find('.credits-feed-amount-btn').on('click', function() {
            $modal.find('.credits-feed-amount-btn').removeClass('selected');
            $(this).addClass('selected');
        });

        // Submit feed
        $modal.find('.credits-feed-submit').on('click', function() {
            var $btn = $(this);
            var amount = $modal.find('.credits-feed-amount-btn.selected').data('amount');

            $btn.prop('disabled', true).text(gdn.definition('Credits.Processing', '处理中...'));

            $.ajax({
                url: gdn.definition('Credits.FeedUrl', '/credits/feed'),
                type: 'POST',
                dataType: 'json',
                data: {
                    ToUserID: toUserID,
                    Amount: amount,
                    DiscussionID: discussionID,
                    TransientKey: gdn.definition('TransientKey')
                },
                success: function(response) {
                    if (response.Success) {
                        gdn.inform(response.Message);
                        closeFeedModal();
                    } else {
                        gdn.informError(response.Message);
                        $btn.prop('disabled', false).text(gdn.definition('Credits.DoFeed', '确认投喂'));
                    }
                },
                error: function() {
                    gdn.informError(gdn.definition('Credits.FeedError', '投喂失败，请稍后重试'));
                    $btn.prop('disabled', false).text(gdn.definition('Credits.DoFeed', '确认投喂'));
                }
            });
        });
    }

    /**
     * Close feed modal.
     */
    function closeFeedModal() {
        var $modal = $('#credits-feed-modal');
        $modal.removeClass('show');
        setTimeout(function() {
            $modal.remove();
        }, 200);
    }

    /**
     * Get current discussion ID from page.
     *
     * @return {number}
     */
    function getDiscussionID() {
        // Try to get from URL
        var match = window.location.pathname.match(/\/post-(\d+)/);
        if (match) {
            return parseInt(match[1], 10);
        }

        // Try to get from page data
        var discussionID = gdn.definition('DiscussionID');
        if (discussionID) {
            return parseInt(discussionID, 10);
        }

        // Try to get from DOM
        var $discussion = $('.Discussion[data-discussionid]');
        if ($discussion.length) {
            return parseInt($discussion.data('discussionid'), 10);
        }

        return 0;
    }

    // Expose API for external use
    window.Credits = {
        showFeedModal: showFeedModal,
        createFeedButton: createFeedButton
    };

})(jQuery);
