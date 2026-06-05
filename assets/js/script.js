// Custom JavaScript for Online Matrimonial Website

$(document).ready(function() {
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    
    // Auto-hide flash messages after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Profile photo preview
    $('#profile_photo').change(function(e) {
        previewImage(e, '#photo_preview');
    });
    
    $('#kundali_file').change(function(e) {
        var fileName = e.target.files[0].name;
        $('#kundali_preview').text('Selected file: ' + fileName);
    });
    
    // Form validation
    $('.needs-validation').on('submit', function(event) {
        if (!this.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        $(this).addClass('was-validated');
    });
    
    // Search form enhancement
    $('#religion').change(function() {
        var religion = $(this).val();
        if (religion) {
            loadCastes(religion);
        } else {
            $('#caste').html('<option value="">Select Caste</option>');
        }
    });
    
    // Age range slider
    if ($('#age_range').length) {
        $('#age_range').slider({
            range: true,
            min: 18,
            max: 60,
            values: [18, 35],
            slide: function(event, ui) {
                $('#age_from').val(ui.values[0]);
                $('#age_to').val(ui.values[1]);
                $('#age_display').text(ui.values[0] + ' - ' + ui.values[1] + ' years');
            }
        });
        $('#age_display').text('18 - 35 years');
    }
    
    // Height range slider
    if ($('#height_range').length) {
        $('#height_range').slider({
            range: true,
            min: 140,
            max: 200,
            values: [150, 180],
            slide: function(event, ui) {
                $('#height_from').val(ui.values[0]);
                $('#height_to').val(ui.values[1]);
                $('#height_display').text(ui.values[0] + 'cm - ' + ui.values[1] + 'cm');
            }
        });
        $('#height_display').text('150cm - 180cm');
    }
    
    // Interest buttons
    $('.send-interest-btn').click(function(e) {
        e.preventDefault();
        var receiverId = $(this).data('receiver-id');
        var btn = $(this);
        
        if (confirm('Are you sure you want to send interest to this profile?')) {
            $.ajax({
                url: SITE_URL + 'user/send_interest.php',
                method: 'POST',
                data: { receiver_id: receiverId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        btn.prop('disabled', true).text('Interest Sent');
                        showAlert(response.message, 'success');
                    } else {
                        showAlert(response.message, 'danger');
                    }
                },
                error: function() {
                    showAlert('An error occurred. Please try again.', 'danger');
                }
            });
        }
    });
    
    // Shortlist buttons
    $('.shortlist-btn').click(function(e) {
        e.preventDefault();
        var profileId = $(this).data('profile-id');
        var btn = $(this);
        var action = btn.hasClass('shortlisted') ? 'remove' : 'add';
        
        $.ajax({
            url: SITE_URL + 'user/shortlist.php',
            method: 'POST',
            data: { profile_id: profileId, action: action },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (action === 'add') {
                        btn.addClass('shortlisted btn-danger').removeClass('btn-outline-danger').text('Shortlisted');
                    } else {
                        btn.removeClass('shortlisted btn-danger').addClass('btn-outline-danger').text('Shortlist');
                    }
                    showAlert(response.message, 'success');
                } else {
                    showAlert(response.message, 'danger');
                }
            },
            error: function() {
                showAlert('An error occurred. Please try again.', 'danger');
            }
        });
    });
    
    // Message sending
    $('#send_message_btn').click(function(e) {
        e.preventDefault();
        var receiverId = $('#receiver_id').val();
        var message = $('#message_text').val().trim();
        
        if (!message) {
            showAlert('Please enter a message', 'warning');
            return;
        }
        
        $.ajax({
            url: SITE_URL + 'user/send_message.php',
            method: 'POST',
            data: { receiver_id: receiverId, message: message },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#message_text').val('');
                    loadMessages(receiverId);
                    showAlert(response.message, 'success');
                } else {
                    showAlert(response.message, 'danger');
                }
            },
            error: function() {
                showAlert('An error occurred. Please try again.', 'danger');
            }
        });
    });
    
    // Load messages
    function loadMessages(userId) {
        $.ajax({
            url: SITE_URL + 'user/get_messages.php',
            method: 'GET',
            data: { user_id: userId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayMessages(response.messages);
                }
            }
        });
    }
    
    // Display messages
    function displayMessages(messages) {
        var container = $('#messages_container');
        container.empty();
        
        messages.forEach(function(msg) {
            var messageClass = msg.sender_id == currentUserId ? 'message-sent' : 'message-received';
            var messageHtml = `
                <div class="message-bubble ${messageClass}">
                    <div>${msg.message}</div>
                    <div class="message-time">${formatTime(msg.created_at)}</div>
                </div>
            `;
            container.append(messageHtml);
        });
        
        container.scrollTop(container[0].scrollHeight);
    }
    
    // Profile view tracking
    $('.profile-link').click(function(e) {
        var profileId = $(this).data('profile-id');
        $.ajax({
            url: SITE_URL + 'user/track_view.php',
            method: 'POST',
            data: { profile_id: profileId }
        });
    });
    
    // Search filters toggle
    $('#toggle_advanced_search').click(function() {
        $('#advanced_search_filters').slideToggle();
        $(this).text($(this).text() === 'Show Advanced Search' ? 'Hide Advanced Search' : 'Show Advanced Search');
    });
    
    // Password strength indicator
    $('#password').on('input', function() {
        var password = $(this).val();
        var strength = checkPasswordStrength(password);
        $('#password_strength').removeClass().addClass('password-strength ' + strength.class).text(strength.text);
    });
    
    // Confirm password validation
    $('#confirm_password').on('input', function() {
        var password = $('#password').val();
        var confirmPassword = $(this).val();
        
        if (password !== confirmPassword) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid').addClass('is-valid');
        }
    });
    
    // Auto-save draft messages
    var messageTimeout;
    $('#message_text').on('input', function() {
        clearTimeout(messageTimeout);
        var message = $(this).val();
        
        messageTimeout = setTimeout(function() {
            localStorage.setItem('draft_message', message);
        }, 1000);
    });
    
    // Load draft message
    var draftMessage = localStorage.getItem('draft_message');
    if (draftMessage) {
        $('#message_text').val(draftMessage);
    }
    
    // Clear draft on send
    $('#send_message_btn').click(function() {
        localStorage.removeItem('draft_message');
    });
    
    // Lazy loading for images
    $('.lazy').lazy({
        effect: 'fadeIn',
        effectTime: 500
    });
    
    // Smooth scroll
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if (target.length) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 70
            }, 1000);
        }
    });
    
    // Back to top button
    $(window).scroll(function() {
        if ($(this).scrollTop() > 200) {
            $('#back_to_top').fadeIn();
        } else {
            $('#back_to_top').fadeOut();
        }
    });
    
    $('#back_to_top').click(function() {
        $('html, body').animate({scrollTop: 0}, 800);
    });
});

// Helper functions
function previewImage(e, selector) {
    var file = e.target.files[0];
    var reader = new FileReader();
    
    reader.onload = function(event) {
        $(selector).attr('src', event.target.result).show();
    };
    
    if (file) {
        reader.readAsDataURL(file);
    }
}

function showAlert(message, type) {
    var alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('.container').first().prepend(alertHtml);
    
    setTimeout(function() {
        $('.alert').first().fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
}

function loadCastes(religion) {
    $.ajax({
        url: SITE_URL + 'includes/get_castes.php',
        method: 'GET',
        data: { religion: religion },
        dataType: 'json',
        success: function(response) {
            var casteSelect = $('#caste');
            casteSelect.empty().append('<option value="">Select Caste</option>');
            
            response.castes.forEach(function(caste) {
                casteSelect.append(`<option value="${caste.caste_name}">${caste.caste_name}</option>`);
            });
        }
    });
}

function checkPasswordStrength(password) {
    var strength = 0;
    
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]+/)) strength++;
    if (password.match(/[A-Z]+/)) strength++;
    if (password.match(/[0-9]+/)) strength++;
    if (password.match(/[$@#&!]+/)) strength++;
    
    if (strength < 2) {
        return { class: 'weak', text: 'Weak password' };
    } else if (strength < 4) {
        return { class: 'medium', text: 'Medium strength' };
    } else {
        return { class: 'strong', text: 'Strong password' };
    }
}

function formatTime(datetime) {
    var date = new Date(datetime);
    var now = new Date();
    var diff = now - date;
    
    if (diff < 60000) {
        return 'Just now';
    } else if (diff < 3600000) {
        return Math.floor(diff / 60000) + ' minutes ago';
    } else if (diff < 86400000) {
        return Math.floor(diff / 3600000) + ' hours ago';
    } else if (diff < 604800000) {
        return Math.floor(diff / 86400000) + ' days ago';
    } else {
        return date.toLocaleDateString();
    }
}

function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Global variables
var SITE_URL = '<?php echo SITE_URL; ?>';
var currentUserId = <?php echo is_logged_in() ? get_user_id() : 'null'; ?>;
