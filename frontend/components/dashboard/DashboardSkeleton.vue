<template>
  <div class="dashboard-skeleton">
    <!-- Stats Row Skeleton -->
    <div class="stats-row">
      <div class="skeleton-card skeleton-stats" v-for="i in 4" :key="i">
        <div class="skeleton-icon"></div>
        <div class="skeleton-content">
          <div class="skeleton-line skeleton-line--sm"></div>
          <div class="skeleton-line skeleton-line--lg"></div>
        </div>
      </div>
    </div>
    
    <!-- Charts Row Skeleton -->
    <div class="charts-row">
      <div class="skeleton-card skeleton-chart">
        <div class="skeleton-line skeleton-line--header"></div>
        <div class="skeleton-circle"></div>
      </div>
      <div class="skeleton-card skeleton-chart">
        <div class="skeleton-line skeleton-line--header"></div>
        <div class="skeleton-bars">
          <div class="skeleton-bar" v-for="i in 3" :key="i"></div>
        </div>
      </div>
    </div>
    
    <!-- Content Row Skeleton -->
    <div class="content-row">
      <div class="skeleton-card skeleton-list">
        <div class="skeleton-line skeleton-line--header"></div>
        <div class="skeleton-item" v-for="i in 4" :key="i">
          <div class="skeleton-badge"></div>
          <div class="skeleton-content-lines">
            <div class="skeleton-line skeleton-line--title"></div>
            <div class="skeleton-line skeleton-line--sm"></div>
          </div>
        </div>
      </div>
      <div class="skeleton-card skeleton-list">
        <div class="skeleton-line skeleton-line--header"></div>
        <div class="skeleton-item" v-for="i in 3" :key="i">
          <div class="skeleton-avatars"></div>
          <div class="skeleton-content-lines">
            <div class="skeleton-line skeleton-line--title"></div>
            <div class="skeleton-line skeleton-line--progress"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Dashboard loading skeleton
</script>

<style scoped>
.dashboard-skeleton {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.charts-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.content-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.skeleton-card {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.03) 100%);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 20px;
  padding: 24px;
}

.skeleton-stats {
  display: flex;
  align-items: flex-start;
  gap: 16px;
}

.skeleton-icon {
  width: 56px;
  height: 56px;
  border-radius: 16px;
  background: linear-gradient(90deg, rgba(255,255,255,0.05) 25%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.05) 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.skeleton-content-lines {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.skeleton-line {
  border-radius: 4px;
  background: linear-gradient(90deg, rgba(255,255,255,0.05) 25%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.05) 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-line--sm {
  width: 60%;
  height: 12px;
}

.skeleton-line--lg {
  width: 40%;
  height: 28px;
}

.skeleton-line--header {
  width: 50%;
  height: 20px;
  margin-bottom: 16px;
}

.skeleton-line--title {
  width: 70%;
  height: 14px;
}

.skeleton-line--progress {
  width: 100%;
  height: 6px;
  margin-top: 4px;
}

.skeleton-chart {
  height: 320px;
  display: flex;
  flex-direction: column;
}

.skeleton-circle {
  flex: 1;
  max-width: 180px;
  max-height: 180px;
  margin: auto;
  aspect-ratio: 1;
  border-radius: 50%;
  background: linear-gradient(90deg, rgba(255,255,255,0.05) 25%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.05) 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-bars {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 20px;
  justify-content: center;
}

.skeleton-bar {
  height: 24px;
  border-radius: 8px;
  background: linear-gradient(90deg, rgba(255,255,255,0.05) 25%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.05) 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-bar:nth-child(1) { width: 70%; }
.skeleton-bar:nth-child(2) { width: 50%; }
.skeleton-bar:nth-child(3) { width: 30%; }

.skeleton-list {
  display: flex;
  flex-direction: column;
}

.skeleton-item {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.skeleton-item:last-child {
  border-bottom: none;
}

.skeleton-badge {
  width: 32px;
  height: 32px;
  border-radius: 10px;
  flex-shrink: 0;
  background: linear-gradient(90deg, rgba(255,255,255,0.05) 25%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.05) 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-avatars {
  width: 56px;
  height: 32px;
  border-radius: 16px;
  flex-shrink: 0;
  background: linear-gradient(90deg, rgba(255,255,255,0.05) 25%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.05) 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  from {
    background-position: 200% 0;
  }
  to {
    background-position: -200% 0;
  }
}

@media (max-width: 1024px) {
  .stats-row {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .charts-row,
  .content-row {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .stats-row {
    grid-template-columns: 1fr;
  }
}
</style>
