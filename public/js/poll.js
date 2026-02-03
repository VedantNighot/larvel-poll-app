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
                if (response.status === 'success') {
                    renderPoll(response.poll, response.options);
                }
            },
            error: function () {
                $('#poll-content').html('<p class="text-danger">Failed to load poll.</p>');
            }
        });
    }

    function renderPoll(poll, options) {
        $('#poll-title').text(poll.question);

        let html = '<form id="vote-form" data-id="' + poll.id + '">';
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
        html += '</form>';

        $('#poll-content').html(html);
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
