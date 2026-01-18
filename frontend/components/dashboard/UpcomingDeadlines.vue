<template>
  <div class="deadlines-card">
    <div class="deadlines-card__header">
      <h3 class="deadlines-card__title">Upcoming Deadlines</h3>
      <NuxtLink to="/calendar" class="view-all-link">
        View All
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M5 12h14M12 5l7 7-7 7"/>
        </svg>
      </NuxtLink>
    </div>
    <div class="deadlines-card__body">
      <div v-if="loading" class="loading-state">
        <div class="skeleton-item" v-for="i in 3" :key="i">
          <div class="skeleton-badge"></div>
          <div class="skeleton-content">
            <div class="skeleton-title"></div>
            <div class="skeleton-date"></div>
          </div>
          <div class="skeleton-actions"></div>
        </div>
      </div>
      
      <TransitionGroup v-else-if="deadlines.length > 0" name="list" tag="div" class="deadlines-list">
        <div 
          v-for="(task, index) in deadlines" 
          :key="task.task_id || task.id"
          class="deadline-item"
          :class="`deadline-item--${task.urgency}`"
          :style="{ animationDelay: `${index * 100}ms` }"
        >
          <div class="deadline-item__category">
            <span class="category-badge" :class="`category-badge--${task.category?.toLowerCase()}`">
              {{ getCategoryShort(task.category) }}
            </span>
          </div>
          
          <div class="deadline-item__content">
            <h4 class="deadline-item__title">{{ task.title }}</h4>
            <div class="deadline-item__meta">
              <span class="due-badge" :class="`due-badge--${task.urgency}`">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <path d="M12 6v6l4 2"/>
                </svg>
                {{ formatDueDate(task.due_date, task.daysUntilDue) }}
              </span>
            </div>
          </div>
          
          <div class="deadline-item__actions">
            <button 
              class="action-btn action-btn--complete"
              @click="handleComplete(task)"
              title="Mark as complete"
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M20 6L9 17l-5-5"/>
              </svg>
            </button>
            <button 
              class="action-btn action-btn--view"
              @click="emit('view', task)"
              title="View details"
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"/>
                <path d="M2 12s4-8 10-8 10 8 10 8-4 8-10 8-10-8-10-8z"/>
              </svg>
            </button>
          </div>
        </div>
      </TransitionGroup>
      
      <div v-else class="empty-state">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <rect x="3" y="4" width="18" height="18" rx="2"/>
          <line x1="16" y1="2" x2="16" y2="6"/>
          <line x1="8" y1="2" x2="8" y2="6"/>
          <line x1="3" y1="10" x2="21" y2="10"/>
          <path d="M9 16l2 2 4-4"/>
        </svg>
        <p>No upcoming deadlines</p>
        <span>All caught up! 🎉</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Deadline {
  task_id?: number
  id?: number
  title: string
  category?: string
  due_date?: string
  urgency: 'overdue' | 'today' | 'this-week' | 'upcoming'
  daysUntilDue: number
}

interface Props {
  deadlines: Deadline[]
  loading?: boolean
}

withDefaults(defineProps<Props>(), {
  loading: false
})

const emit = defineEmits<{
  complete: [id: number]
  view: [task: Deadline]
}>()

const getCategoryShort = (category?: string) => {
  if (!category) return '-'
  const map: Record<string, string> = {
    personal: 'P',
    school: 'S',
    work: 'W'
  }
  return map[category.toLowerCase()] || category.charAt(0).toUpperCase()
}

const formatDueDate = (dueDate?: string, daysUntilDue?: number) => {
  if (!dueDate) return 'No date'
  
  if (daysUntilDue === undefined) {
    return new Date(dueDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
  }
  
  if (daysUntilDue < 0) {
    const daysOverdue = Math.abs(daysUntilDue)
    return daysOverdue === 1 ? '1 day overdue' : `${daysOverdue} days overdue`
  }
  
  if (daysUntilDue === 0) return 'Due today'
  if (daysUntilDue === 1) return 'Due tomorrow'
  if (daysUntilDue <= 7) return `In ${daysUntilDue} days`
  
  return new Date(dueDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
}

const handleComplete = (task: Deadline) => {
  const id = task.task_id || task.id
  if (id !== undefined) {
    emit('complete', id)
  }
}
</script>

<style scoped>
.deadlines-card {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.03) 100%);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  padding: 24px;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.deadlines-card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}

.deadlines-card__title {
  font-size: 18px;
  font-weight: 700;
  color: white;
  margin: 0;
}

.view-all-link {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 500;
  color: #3b82f6;
  text-decoration: none;
  transition: all 0.2s ease;
}

.view-all-link:hover {
  color: #60a5fa;
  gap: 10px;
}

.deadlines-card__body {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.deadlines-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.deadline-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  transition: all 0.3s ease;
  animation: slideIn 0.4s ease-out both;
}

.deadline-item:hover {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.12);
  transform: translateX(4px);
}

.deadline-item--overdue {
  border-left: 3px solid #F44336;
}

.deadline-item--today {
  border-left: 3px solid #FF9800;
}

.deadline-item--this-week {
  border-left: 3px solid #FFC107;
}

.deadline-item--upcoming {
  border-left: 3px solid #4CAF50;
}

.category-badge {
  width: 32px;
  height: 32px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 700;
  color: white;
}

.category-badge--personal {
  background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%);
}

.category-badge--school {
  background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%);
}

.category-badge--work {
  background: linear-gradient(135deg, #607D8B 0%, #455A64 100%);
}

.deadline-item__content {
  flex: 1;
  min-width: 0;
}

.deadline-item__title {
  font-size: 14px;
  font-weight: 600;
  color: white;
  margin: 0 0 6px 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.deadline-item__meta {
  display: flex;
  align-items: center;
  gap: 8px;
}

.due-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  font-weight: 500;
  padding: 4px 8px;
  border-radius: 6px;
}

.due-badge--overdue {
  background: rgba(244, 67, 54, 0.15);
  color: #F44336;
}

.due-badge--today {
  background: rgba(255, 152, 0, 0.15);
  color: #FF9800;
}

.due-badge--this-week {
  background: rgba(255, 193, 7, 0.15);
  color: #FFC107;
}

.due-badge--upcoming {
  background: rgba(76, 175, 80, 0.15);
  color: #4CAF50;
}

.deadline-item__actions {
  display: flex;
  gap: 8px;
}

.action-btn {
  width: 32px;
  height: 32px;
  border-radius: 10px;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-btn--complete {
  background: rgba(76, 175, 80, 0.15);
  color: #4CAF50;
}

.action-btn--complete:hover {
  background: rgba(76, 175, 80, 0.3);
  transform: scale(1.1);
}

.action-btn--view {
  background: rgba(59, 130, 246, 0.15);
  color: #3b82f6;
}

.action-btn--view:hover {
  background: rgba(59, 130, 246, 0.3);
  transform: scale(1.1);
}

.empty-state {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  color: rgba(255, 255, 255, 0.4);
  text-align: center;
  padding: 20px;
}

.empty-state p {
  margin: 0;
  font-size: 14px;
  font-weight: 500;
}

.empty-state span {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.3);
}

.loading-state {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.skeleton-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  background: rgba(255, 255, 255, 0.04);
  border-radius: 14px;
}

.skeleton-badge {
  width: 32px;
  height: 32px;
  border-radius: 10px;
  background: linear-gradient(90deg, rgba(255,255,255,0.05) 25%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.05) 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-content {
  flex: 1;
}

.skeleton-title {
  width: 60%;
  height: 14px;
  border-radius: 4px;
  background: linear-gradient(90deg, rgba(255,255,255,0.05) 25%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.05) 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  margin-bottom: 8px;
}

.skeleton-date {
  width: 40%;
  height: 10px;
  border-radius: 4px;
  background: linear-gradient(90deg, rgba(255,255,255,0.05) 25%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.05) 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-actions {
  width: 72px;
  height: 32px;
  border-radius: 10px;
  background: linear-gradient(90deg, rgba(255,255,255,0.05) 25%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.05) 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(-20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes shimmer {
  from {
    background-position: 200% 0;
  }
  to {
    background-position: -200% 0;
  }
}

/* Transition styles */
.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}

.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}

.list-move {
  transition: transform 0.3s ease;
}
</style>
