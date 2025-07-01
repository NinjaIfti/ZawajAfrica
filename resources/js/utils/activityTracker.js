import { csrfUtils } from './csrf';

class ActivityTracker {
    constructor() {
        this.heartbeatInterval = null;
        this.lastActivity = Date.now();
        this.isActive = true;
        this.heartbeatFrequency = 30000; // 30 seconds
        this.inactivityThreshold = 300000; // 5 minutes
        
        this.init();
    }
    
    init() {
        // Track user interactions
        this.bindActivityEvents();
        
        // Start heartbeat
        this.startHeartbeat();
        
        // Handle page visibility
        this.handleVisibilityChange();
    }
    
    bindActivityEvents() {
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        
        events.forEach(event => {
            document.addEventListener(event, () => {
                this.updateActivity();
            }, { passive: true });
        });
    }
    
    updateActivity() {
        this.lastActivity = Date.now();
        if (!this.isActive) {
            this.isActive = true;
            this.sendHeartbeat();
        }
    }
    
    startHeartbeat() {
        this.heartbeatInterval = setInterval(() => {
            const now = Date.now();
            const timeSinceLastActivity = now - this.lastActivity;
            
            if (timeSinceLastActivity < this.inactivityThreshold) {
                this.sendHeartbeat();
            } else {
                this.isActive = false;
            }
        }, this.heartbeatFrequency);
    }
    
    async sendHeartbeat() {
        try {
            await fetch('/user/activity', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfUtils.getToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    timestamp: Date.now()
                })
            });
        } catch (error) {
            console.error('Failed to send activity heartbeat:', error);
        }
    }
    
    handleVisibilityChange() {
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.isActive = false;
            } else {
                this.updateActivity();
            }
        });
    }
    
    stop() {
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
            this.heartbeatInterval = null;
        }
    }
}

// Create a singleton instance
const activityTracker = new ActivityTracker();

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    activityTracker.stop();
});

export default activityTracker; 