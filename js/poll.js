$(document).ready(function () {

    // Click on a poll in the list
    $('.poll-item').on('click', function (e) {
        e.preventDefault();

        // Highlight selected
        $('.poll-item').removeClass('active');
        $(this).addClass('active');

        let pollId = $(this).data('id');
        loadPoll(pollId);
    });

    function loadPoll(id) {
        $('#poll-content').html('<div class="text-center"><div class="spinner-border text-primary"></div></div>');

        $.ajax({
            url: '/polls/' + id + '/options',
            type: 'GET',
            success: function (response) {
                // Handle text response that might contain Fatal Error (PHP Dump)
                if (typeof response === 'string') {
                    // Try to parse JSON manually if it's a string
                    try {
                        response = JSON.parse(response);
                    } catch (e) {
                        $('#poll-content').html('<div class="alert alert-danger">Server Error: ' + response + '</div>');
                        return;
                    }
                }

                if (response.status === 'success') {
                    renderPoll(response.poll, response.options);
                } else {
                    $('#poll-content').html('<div class="alert alert-warning">Error: ' + (response.message || 'Unknown error') + '</div>');
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error);
                console.log(xhr.responseText);
                $('#poll-content').html('<div class="alert alert-danger">Failed to load poll. Status: ' + xhr.status + '</div>');
            }
        });
    }

    function renderPoll(poll, options) {
        try {
            $('#poll-title').text(poll.question);

            // Safety: Ensure options is an array
            if (!Array.isArray(options)) {
                // Try converting object to array
                options = Object.values(options);
            }

            let html = '<form id="vote-form" data-id="' + poll.id + '">';
            if (options.length === 0) {
                html += '<p class="text-warning">No options found for this poll.</p>';
            } else {
                options.forEach(function (opt) {
                    html += `
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="option_id" id="opt-${opt.id}" value="${opt.id}">
                            <label class="form-check-label" for="opt-${opt.id}">
                                ${opt.option_text}
                            </label>
                        </div>
                    `;
                });
                html += '<button type="submit" class="btn btn-primary mt-3">Vote</button>';
            }
            html += '</form>';

            $('#poll-content').html(html);
        } catch (e) {
            console.error(e);
            $('#poll-content').html('<div class="alert alert-danger">JS Error: ' + e.message + '</div>');
        }
    }

    // Vote Submission logic
    $(document).on('submit', '#vote-form', function (e) {
        e.preventDefault();

        // Basic validation
        if (!$('input[name="option_id"]:checked').val()) {
            alert('Please select an option!');
            return;
        }

        let pollId = $(this).data('id');
        let formData = $(this).serialize();
        let btn = $(this).find('button');

        // Disable button
        btn.prop('disabled', true).text('Voting...');

        $.ajax({
            url: '/polls/' + pollId + '/vote',
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                $('#poll-content').html(`
                    <div class="alert alert-success text-center">
                        <h4 class="alert-heading">Vote Submitted!</h4>
                        <p>${response.message}</p>
                    </div>
                `);
            },
            error: function (xhr) {
                let msg = 'An error occurred.';
                if (xhr.status === 403) {
                    msg = xhr.responseJSON.message;
                }

                // Re-enable button but show error
                btn.prop('disabled', false).text('Vote');
                if ($('#error-msg').length === 0) {
                    $('#vote-form').prepend('<div id="error-msg" class="alert alert-danger">' + msg + '</div>');
                } else {
                    $('#error-msg').text(msg);
                }
            }
        });
    });
});
