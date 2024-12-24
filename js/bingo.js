jQuery(document).ready(function($) {
    $('.bingo-slot-completion').on('change', function() {
        var checkbox = $(this);
        var nameAttr = checkbox.attr('name');

        if (nameAttr) {
            var slotIndex = nameAttr.match(/\[(\d+)\]/); 
            if (slotIndex && slotIndex[1]) {
                slotIndex = slotIndex[1]; 
            } else {
                return;
            }
            var isChecked = checkbox.prop('checked') ? 1 : 0;
            var postId = checkbox.data('post-id');
            if (!postId) {
                return;
            }
            $.ajax({
                url: bingo_post_data.ajaxurl,
                type: 'POST',
                data: {
                    action: 'save_bingo_slot_status',
                    post_id: postId, 
                    slot_index: slotIndex,
                    is_completed: isChecked 
                },
                error: function() {
                    console.log('AJAX request failed');
                }
            });
        }
    });
});
