<template>
  <div class="projects-card">
    <div class="projects-card__header">
      <h3 class="projects-card__title">Collaborations</h3>
      <NuxtLink to="/projects" class="view-all-link">
        View All
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M5 12h14M12 5l7 7-7 7"/>
        </svg>
      </NuxtLink>
    </div>
    
    <div class="projects-card__body">
      <div v-if="loading" class="loading-state">
        <div class="skeleton-project" v-for="i in 3" :key="i">
          <div class="skeleton-avatars"></div>
          <div class="skeleton-content">
            <div class="skeleton-title"></div>
            <div class="skeleton-progress"></div>
          </div>
        </div>
      </div>
      
      <TransitionGroup v-else-if="projects.length > 0" name="list" tag="div" class="projects-list">
        <div 
          v-for="(project, index) in projects" 
          :key="project.task_id || project.id"
          class="project-item"
          :style="{ animationDelay: `${index * 100}ms` }"
          @click="$emit('view', project)"
        >
          <div class="project-item__avatars">
            <div 
              v-for="(collab, idx) in getCollaborators(project).slice(0, 3)" 
              :key="idx"
              class="avatar"
              :style="{ 
                zIndex: 3 - idx,
                background: getAvatarColor(idx)
              }"
            >
              <img 
                v-if="collab.user?.profile_picture" 
                :src="collab.user.profile_picture" 
                :alt="getCollaboratorName(collab)"
              />
              <span v-else>{{ getCollaboratorInitial(collab) }}</span>
            </div>
            <div 
              v-if="getCollaborators(project).length > 3" 
              class="avatar avatar--more"
            >
              +{{ getCollaborators(project).length - 3 }}
            </div>
          </div>
          
          <div class="project-item__content">
            <h4 class="project-item__title">{{ project.title }}</h4>
            <div class="project-item__progress">
              <div class="progress-bar">
                <div 
                  class="progress-bar__fill" 
                  :style="{ width: `${project.progress || 0}%` }"
                ></div>
              </div>
              <span class="progress-text">{{ project.progress || 0 }}%</span>
            </div>
          </div>
          
          <div class="project-item__status">
            <span class="status-badge" :class="`status-badge--${project.status?.toLowerCase()}`">
              {{ formatStatus(project.status) }}
            </span>
          </div>
        </div>
      </TransitionGroup>
      
      <div v-else class="empty-state">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
          <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
        <p>No collaborative projects</p>
        <span>Start collaborating with your team</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Collaborator {
  user?: {
    profile_picture?: string
    fname?: string
    lname?: string
    email?: string
  }
  user_id?: number
}

interface Project {
  task_id?: number
  id?: number
  title: string
  status?: string
  progress?: number
  task_collaborators?: Collaborator[]
  collaborators?: Collaborator[]
}

interface Props {
  projects: Project[]
  loading?: boolean
}

withDefaults(defineProps<Props>(), {
  loading: false
})

defineEmits<{
  view: [project: Project]
}>()

const getCollaborators = (project: Project) => {
  return project.task_collaborators || project.collaborators || []
}

const getCollaboratorName = (collab: Collaborator) => {
  if (!collab.user) return 'User'
  const { fname, lname, email } = collab.user
  if (fname || lname) return `${fname || ''} ${lname || ''}`.trim()
  return email?.split('@')[0] || 'User'
}

const getCollaboratorInitial = (collab: Collaborator) => {
  const name = getCollaboratorName(collab)
  return name.charAt(0).toUpperCase()
}

const getAvatarColor = (index: number) => {
  const colors = [
    'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)',
    'linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%)',
    'linear-gradient(135deg, #ec4899 0%, #db2777 100%)',
    'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)'
  ]
  return colors[index % colors.length]
}

const formatStatus = (status?: string) => {
  if (!status) return 'Unknown'
  const map: Record<string, string> = {
    pending: 'Pending',
    ongoing: 'In Progress',
    in_progress: 'In Progress',
    completed: 'Done'
  }
  return map[status.toLowerCase()] || status
}
</script>

<style scoped>
.projects-card {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.03) 100%);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  padding: 24px;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.projects-card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}

.projects-card__title {
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

.projects-card__body {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.projects-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.project-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
  animation: slideIn 0.4s ease-out both;
}

.project-item:hover {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.12);
  transform: translateX(4px);
}

.project-item__avatars {
  display: flex;
  flex-shrink: 0;
}

.avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 600;
  color: white;
  border: 2px solid rgba(15, 22, 35, 1);
  margin-left: -8px;
  overflow: hidden;
}

.avatar:first-child {
  margin-left: 0;
}

.avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar--more {
  background: rgba(255, 255, 255, 0.2);
  font-size: 10px;
}

.project-item__content {
  flex: 1;
  min-width: 0;
}

.project-item__title {
  font-size: 14px;
  font-weight: 600;
  color: white;
  margin: 0 0 8px 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.project-item__progress {
  display: flex;
  align-items: center;
  gap: 10px;
}

.progress-bar {
  flex: 1;
  height: 6px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 3px;
  overflow: hidden;
}

.progress-bar__fill {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6 0%, #60a5fa 100%);
  border-radius: 3px;
  transition: width 0.8s ease-out;
}

.progress-text {
  font-size: 12px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.6);
  min-width: 35px;
  text-align: right;
}

.project-item__status {
  flex-shrink: 0;
}

.status-badge {
  font-size: 11px;
  font-weight: 600;
  padding: 5px 10px;
  border-radius: 8px;
  white-space: nowrap;
}

.status-badge--pending {
  background: rgba(255, 193, 7, 0.15);
  color: #FFC107;
}

.status-badge--ongoing,
.status-badge--in_progress {
  background: rgba(33, 150, 243, 0.15);
  color: #2196F3;
}

.status-badge--completed {
  background: rgba(76, 175, 80, 0.15);
  color: #4CAF50;
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

.skeleton-project {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  background: rgba(255, 255, 255, 0.04);
  border-radius: 14px;
}

.skeleton-avatars {
  width: 56px;
  height: 32px;
  border-radius: 16px;
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
  margin-bottom: 10px;
}

.skeleton-progress {
  width: 100%;
  height: 6px;
  border-radius: 3px;
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
