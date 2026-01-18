<template>
  <div v-if="overdueTasks.length > 0" class="overdue-alert">
    <div class="overdue-alert__icon">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="6" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
    </div>
    
    <div class="overdue-alert__content">
      <div class="overdue-alert__header">
        <h3 class="overdue-alert__title">
          {{ overdueTasks.length }} Overdue {{ overdueTasks.length === 1 ? 'Task' : 'Tasks' }}
        </h3>
        <span class="overdue-alert__badge">Urgent Action Required</span>
      </div>
      
      <div class="overdue-list">
        <div 
          v-for="task in overdueTasks.slice(0, 3)" 
          :key="task.task_id || task.id" 
          class="overdue-item"
        >
          <span class="overdue-item__title">{{ task.title }}</span>
          <span class="overdue-item__days">
            {{ task.daysOverdue }} {{ task.daysOverdue === 1 ? 'day' : 'days' }} overdue
          </span>
          <button 
            class="reschedule-btn"
            @click="$emit('reschedule', task)"
          >
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2"/>
              <line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="8" y1="2" x2="8" y2="6"/>
              <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            Reschedule
          </button>
        </div>
        
        <div v-if="overdueTasks.length > 3" class="overdue-more">
          +{{ overdueTasks.length - 3 }} more overdue tasks
        </div>
      </div>
    </div>
    
    <button class="overdue-alert__close" @click="$emit('dismiss')">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="18" y1="6" x2="6" y2="18"/>
        <line x1="6" y1="6" x2="18" y2="18"/>
      </svg>
    </button>
  </div>
</template>

<script setup lang="ts">
interface OverdueTask {
  task_id?: number
  id?: number
  title: string
  due_date?: string
  daysOverdue: number
}

interface Props {
  overdueTasks: OverdueTask[]
}

defineProps<Props>()

defineEmits<{
  reschedule: [task: OverdueTask]
  dismiss: []
}>()
</script>

<style scoped>
.overdue-alert {
  background: linear-gradient(135deg, rgba(244, 67, 54, 0.15) 0%, rgba(198, 40, 40, 0.1) 100%);
  border: 1px solid rgba(244, 67, 54, 0.3);
  border-radius: 20px;
  padding: 20px 24px;
  display: flex;
  gap: 16px;
  position: relative;
  animation: shake 0.5s ease-in-out;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
  20%, 40%, 60%, 80% { transform: translateX(2px); }
}

.overdue-alert__icon {
  flex-shrink: 0;
  width: 52px;
  height: 52px;
  background: rgba(244, 67, 54, 0.2);
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #F44336;
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

.overdue-alert__content {
  flex: 1;
  min-width: 0;
}

.overdue-alert__header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
  flex-wrap: wrap;
}

.overdue-alert__title {
  font-size: 18px;
  font-weight: 700;
  color: #EF5350;
  margin: 0;
}

.overdue-alert__badge {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 4px 10px;
  background: rgba(244, 67, 54, 0.2);
  color: #F44336;
  border-radius: 6px;
}

.overdue-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.overdue-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 14px;
  background: rgba(0, 0, 0, 0.2);
  border-radius: 10px;
}

.overdue-item__title {
  flex: 1;
  font-size: 13px;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.9);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.overdue-item__days {
  font-size: 12px;
  font-weight: 600;
  color: #F44336;
  white-space: nowrap;
}

.reschedule-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  color: white;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.reschedule-btn:hover {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.3);
  transform: translateY(-1px);
}

.overdue-more {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.5);
  padding: 8px 14px;
  text-align: center;
  font-style: italic;
}

.overdue-alert__close {
  position: absolute;
  top: 16px;
  right: 16px;
  width: 28px;
  height: 28px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.1);
  border: none;
  color: rgba(255, 255, 255, 0.5);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.overdue-alert__close:hover {
  background: rgba(255, 255, 255, 0.2);
  color: white;
}
</style>
