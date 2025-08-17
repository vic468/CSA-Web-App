// Session Timeout Management
class SessionManager {
    constructor(options = {}) {
        this.warningTime = options.warningTime || 300; // 5 minutes before expiry
        this.sessionTimeout = options.sessionTimeout || 1800; // 30 minutes
        this.checkInterval = options.checkInterval || 60; // Check every minute
        this.extendUrl = options.extendUrl || 'ajax/extend_session.php';
        
        this.warningShown = false;
        this.lastActivity = Date.now();
        
        this.init();
    }
    
    init() {
        // Track user activity
        this.trackActivity();
        
        // Start session monitoring
        this.startMonitoring();
        
        // Create warning modal
        this.createWarningModal();
    }
    
    trackActivity() {
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        
        events.forEach(event => {
            document.addEventListener(event, () => {
                this.lastActivity = Date.now();
                this.hideWarning();
            }, true);
        });
    }
    
    startMonitoring() {
        setInterval(() => {
            this.checkSession();
        }, this.checkInterval * 1000);
    }
    
    checkSession() {
        const inactiveTime = (Date.now() - this.lastActivity) / 1000;
        const timeRemaining = this.sessionTimeout - inactiveTime;
        
        if (timeRemaining <= 0) {
            // Session expired
            this.handleSessionExpiry();
        } else if (timeRemaining <= this.warningTime && !this.warningShown) {
            // Show warning
            this.showWarning(Math.ceil(timeRemaining / 60));
        }
    }
    
    showWarning(minutesRemaining) {
        this.warningShown = true;
        const modal = document.getElementById('sessionWarningModal');
        const timeSpan = document.getElementById('sessionTimeRemaining');
        
        if (modal && timeSpan) {
            timeSpan.textContent = minutesRemaining;
            modal.style.display = 'flex';
            
            // Update countdown every second
            this.countdownInterval = setInterval(() => {
                const inactiveTime = (Date.now() - this.lastActivity) / 1000;
                const timeRemaining = this.sessionTimeout - inactiveTime;
                const minutes = Math.ceil(timeRemaining / 60);
                
                if (minutes <= 0) {
                    this.handleSessionExpiry();
                } else {
                    timeSpan.textContent = minutes;
                }
            }, 1000);
        }
    }
    
    hideWarning() {
        if (this.warningShown) {
            this.warningShown = false;
            const modal = document.getElementById('sessionWarningModal');
            if (modal) {
                modal.style.display = 'none';
            }
            if (this.countdownInterval) {
                clearInterval(this.countdownInterval);
            }
        }
    }
    
    extendSession() {
        fetch(this.extendUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.lastActivity = Date.now();
                this.hideWarning();
                this.showNotification('Session extended successfully', 'success');
            } else {
                this.handleSessionExpiry();
            }
        })
        .catch(error => {
            console.error('Error extending session:', error);
            this.handleSessionExpiry();
        });\n    }\n    \n    handleSessionExpiry() {\n        this.showNotification('Your session has expired. Redirecting to login...', 'warning');\n        setTimeout(() => {\n            window.location.href = 'login.php?error=session_expired';\n        }, 2000);\n    }\n    \n    createWarningModal() {\n        const modalHTML = `\n            <div id=\"sessionWarningModal\" class=\"session-modal\" style=\"display: none;\">\n                <div class=\"session-modal-content\">\n                    <div class=\"session-modal-header\">\n                        <i class=\"fas fa-exclamation-triangle text-warning\"></i>\n                        <h5>Session Expiring Soon</h5>\n                    </div>\n                    <div class=\"session-modal-body\">\n                        <p>Your session will expire in <span id=\"sessionTimeRemaining\">5</span> minute(s) due to inactivity.</p>\n                        <p>Would you like to extend your session?</p>\n                    </div>\n                    <div class=\"session-modal-footer\">\n                        <button type=\"button\" class=\"btn btn-secondary\" onclick=\"sessionManager.handleSessionExpiry()\">Logout</button>\n                        <button type=\"button\" class=\"btn btn-primary\" onclick=\"sessionManager.extendSession()\">Extend Session</button>\n                    </div>\n                </div>\n            </div>\n        `;\n        \n        document.body.insertAdjacentHTML('beforeend', modalHTML);\n    }\n    \n    showNotification(message, type = 'info') {\n        const notification = document.createElement('div');\n        notification.className = `alert alert-${type} session-notification`;\n        notification.innerHTML = `\n            <i class=\"fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'}\"></i>\n            ${message}\n        `;\n        \n        document.body.appendChild(notification);\n        \n        // Auto-remove after 3 seconds\n        setTimeout(() => {\n            notification.remove();\n        }, 3000);\n    }\n}\n\n// CSS for session modal and notifications\nconst sessionCSS = `\n    .session-modal {\n        position: fixed;\n        top: 0;\n        left: 0;\n        width: 100%;\n        height: 100%;\n        background: rgba(0, 0, 0, 0.5);\n        display: flex;\n        justify-content: center;\n        align-items: center;\n        z-index: 10000;\n    }\n    \n    .session-modal-content {\n        background: white;\n        border-radius: 12px;\n        padding: 0;\n        max-width: 400px;\n        width: 90%;\n        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);\n        animation: sessionModalSlideIn 0.3s ease-out;\n    }\n    \n    .session-modal-header {\n        padding: 20px 25px 15px;\n        border-bottom: 1px solid #e9ecef;\n        display: flex;\n        align-items: center;\n        gap: 10px;\n    }\n    \n    .session-modal-header h5 {\n        margin: 0;\n        color: #2d3748;\n        font-weight: 600;\n    }\n    \n    .session-modal-body {\n        padding: 20px 25px;\n        color: #4a5568;\n        line-height: 1.5;\n    }\n    \n    .session-modal-body p {\n        margin: 0 0 10px 0;\n    }\n    \n    .session-modal-footer {\n        padding: 15px 25px 20px;\n        display: flex;\n        gap: 10px;\n        justify-content: flex-end;\n    }\n    \n    .session-notification {\n        position: fixed;\n        top: 20px;\n        right: 20px;\n        z-index: 10001;\n        min-width: 300px;\n        animation: sessionNotificationSlideIn 0.3s ease-out;\n    }\n    \n    @keyframes sessionModalSlideIn {\n        from {\n            opacity: 0;\n            transform: translateY(-50px) scale(0.9);\n        }\n        to {\n            opacity: 1;\n            transform: translateY(0) scale(1);\n        }\n    }\n    \n    @keyframes sessionNotificationSlideIn {\n        from {\n            opacity: 0;\n            transform: translateX(100%);\n        }\n        to {\n            opacity: 1;\n            transform: translateX(0);\n        }\n    }\n`;\n\n// Inject CSS\nconst style = document.createElement('style');\nstyle.textContent = sessionCSS;\ndocument.head.appendChild(style);\n\n// Initialize session manager when DOM is ready\nlet sessionManager;\ndocument.addEventListener('DOMContentLoaded', function() {\n    sessionManager = new SessionManager();\n});
