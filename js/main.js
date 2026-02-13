// js/main.js

$(document).ready(function() {
    // Initialize all components
    initSearch();
    initMobileMenu();
    initInfiniteScroll();
    initReadingMode();
    initBookmarkButtons();
    
    // Smooth scroll untuk anchor links
    $('a[href*="#"]:not([href="#"])').click(function() {
        if (location.pathname.replace(/^\//, '') === this.pathname.replace(/^\//, '') 
            && location.hostname === this.hostname) {
            const target = $(this.hash);
            target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 800);
                return false;
            }
        }
    });
});

// Search functionality
function initSearch() {
    let searchTimeout;
    
    $('#searchInput').on('keyup', function(e) {
        clearTimeout(searchTimeout);
        const query = $(this).val();
        
        if (e.which === 13) { // Enter key
            e.preventDefault();
            $('#searchForm').submit();
            return;
        }
        
        if (query.length >= 3) {
            searchTimeout = setTimeout(function() {
                performSearch(query);
            }, 500);
        } else {
            $('#searchResults').removeClass('active').empty();
        }
    });
    
    // Close search results when clicking outside
    $(document).click(function(e) {
        if (!$(e.target).closest('.header-search').length) {
            $('#searchResults').removeClass('active').empty();
        }
    });
}

function performSearch(query) {
    $.ajax({
        url: 'ajax/search-suggest.php',
        method: 'GET',
        data: { q: query },
        beforeSend: function() {
            $('#searchResults').html('<div class="loading">Mencari...</div>').addClass('active');
        },
        success: function(response) {
            if (response.trim()) {
                $('#searchResults').html(response).addClass('active');
            } else {
                $('#searchResults').html('<div class="no-results">Tidak ada hasil</div>').addClass('active');
            }
        },
        error: function() {
            $('#searchResults').html('<div class="error">Terjadi kesalahan</div>').addClass('active');
        }
    });
}

// Mobile menu
function initMobileMenu() {
    $('.mobile-menu-toggle').click(function() {
        $('.main-nav').slideToggle(300);
        $(this).find('i').toggleClass('fa-bars fa-times');
    });
    
    // Close menu when clicking outside
    $(document).click(function(e) {
        if (!$(e.target).closest('.header').length) {
            if ($('.main-nav').is(':visible') && $(window).width() <= 768) {
                $('.main-nav').slideUp(300);
                $('.mobile-menu-toggle i').removeClass('fa-times').addClass('fa-bars');
            }
        }
    });
    
    // Handle window resize
    $(window).resize(function() {
        if ($(window).width() > 768) {
            $('.main-nav').show();
            $('.mobile-menu-toggle i').removeClass('fa-times').addClass('fa-bars');
        } else {
            $('.main-nav').hide();
        }
    });
}

// Infinite scroll
function initInfiniteScroll() {
    let page = 2;
    let loading = false;
    let hasMore = true;
    
    $(window).scroll(function() {
        if (!hasMore || loading) return;
        
        const scrollTop = $(window).scrollTop();
        const windowHeight = $(window).height();
        const documentHeight = $(document).height();
        
        if (scrollTop + windowHeight >= documentHeight - 500) {
            loading = true;
            loadMoreContent(page, function(success, more) {
                loading = false;
                if (success) {
                    page++;
                    hasMore = more;
                }
            });
        }
    });
}

function loadMoreContent(page, callback) {
    showLoading();
    
    $.ajax({
        url: 'ajax/load-more.php',
        method: 'GET',
        data: { page: page },
        dataType: 'html',
        success: function(response) {
            hideLoading();
            if (response.trim()) {
                $('.novel-grid').append(response);
                callback(true, true);
            } else {
                callback(true, false);
            }
        },
        error: function() {
            hideLoading();
            showNotification('Gagal memuat konten', 'error');
            callback(false, false);
        }
    });
}

// Reading mode
function initReadingMode() {
    // Load saved preference
    const savedMode = localStorage.getItem('reading_mode');
    if (savedMode) {
        $('.reading-content').addClass(savedMode + '-mode');
        $('.reading-mode-btn i').attr('class', savedMode === 'dark' ? 'fas fa-sun' : 'fas fa-moon');
    }
    
    // Toggle reading mode
    $('.reading-mode-btn').click(function() {
        $('.reading-content').toggleClass('dark-mode light-mode');
        const isDark = $('.reading-content').hasClass('dark-mode');
        const mode = isDark ? 'dark' : 'light';
        
        localStorage.setItem('reading_mode', mode);
        $(this).find('i').attr('class', isDark ? 'fas fa-sun' : 'fas fa-moon');
        
        showNotification(`Mode ${isDark ? 'gelap' : 'terang'} diaktifkan`, 'info');
    });
}

// Bookmark functionality
function initBookmarkButtons() {
    $('.bookmark-btn').click(function() {
        const novelId = $(this).data('id');
        const isBookmarked = $(this).hasClass('active');
        
        if (isBookmarked) {
            removeBookmark(novelId, $(this));
        } else {
            addBookmark(novelId, $(this));
        }
    });
}

function addBookmark(novelId, btn) {
    if (!isLoggedIn()) {
        showNotification('Silakan login terlebih dahulu', 'warning');
        setTimeout(function() {
            window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
        }, 1500);
        return;
    }
    
    $.ajax({
        url: 'ajax/bookmark.php',
        method: 'POST',
        data: { 
            action: 'add',
            novel_id: novelId 
        },
        dataType: 'json',
        beforeSend: function() {
            btn.find('i').addClass('fa-spinner fa-spin');
        },
        success: function(response) {
            btn.find('i').removeClass('fa-spinner fa-spin');
            
            if (response.success) {
                btn.addClass('active');
                btn.find('i').removeClass('far').addClass('fas');
                showNotification('Novel ditambahkan ke bookmark', 'success');
            } else {
                showNotification(response.message || 'Gagal menambahkan bookmark', 'error');
            }
        },
        error: function() {
            btn.find('i').removeClass('fa-spinner fa-spin');
            showNotification('Terjadi kesalahan', 'error');
        }
    });
}

function removeBookmark(novelId, btn) {
    $.ajax({
        url: 'ajax/bookmark.php',
        method: 'POST',
        data: { 
            action: 'remove',
            novel_id: novelId 
        },
        dataType: 'json',
        beforeSend: function() {
            btn.find('i').addClass('fa-spinner fa-spin');
        },
        success: function(response) {
            btn.find('i').removeClass('fa-spinner fa-spin');
            
            if (response.success) {
                btn.removeClass('active');
                btn.find('i').removeClass('fas').addClass('far');
                showNotification('Novel dihapus dari bookmark', 'info');
            } else {
                showNotification(response.message || 'Gagal menghapus bookmark', 'error');
            }
        },
        error: function() {
            btn.find('i').removeClass('fa-spinner fa-spin');
            showNotification('Terjadi kesalahan', 'error');
        }
    });
}

// Check login status
function isLoggedIn() {
    return $('body').data('logged-in') === true;
}

// Show/hide loading
function showLoading() {
    $('#loadingSpinner').fadeIn(200);
}

function hideLoading() {
    $('#loadingSpinner').fadeOut(200);
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = $('<div class="notification ' + type + '">' + message + '</div>');
    
    $('#notificationContainer').append(notification);
    
    setTimeout(function() {
        notification.fadeOut(300, function() {
            $(this).remove();
        });
    }, 3000);
}

// Format number
function formatNumber(num) {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    }
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toString();
}

// Parse URL parameters
function getUrlParameter(name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    const results = regex.exec(location.search);
    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Throttle function
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Copy to clipboard
function copyToClipboard(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    document.body.appendChild(textarea);
    textarea.select();
    
    try {
        document.execCommand('copy');
        showNotification('Teks berhasil disalin', 'success');
    } catch (err) {
        showNotification('Gagal menyalin teks', 'error');
    }
    
    document.body.removeChild(textarea);
}

// Detect browser
function getBrowser() {
    const ua = navigator.userAgent;
    if (ua.indexOf('Chrome') > -1) return 'chrome';
    if (ua.indexOf('Firefox') > -1) return 'firefox';
    if (ua.indexOf('Safari') > -1) return 'safari';
    if (ua.indexOf('Edge') > -1) return 'edge';
    if (ua.indexOf('MSIE') > -1 || ua.indexOf('Trident/') > -1) return 'ie';
    return 'unknown';
}

// Image lazy loading
function initLazyLoading() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    } else {
        // Fallback for older browsers
        document.querySelectorAll('img[data-src]').forEach(img => {
            img.src = img.dataset.src;
        });
    }
}

// Initialize on document ready
$(document).ready(function() {
    initLazyLoading();
});
