<template>
  <Teleport to="body">
    <Transition name="modal">
      <div 
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="closeModal"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal"></div>
        
        <!-- Modal Content -->
        <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl shadow-2xl" style="background: #1a1f2e; border: 1px solid rgba(255, 255, 255, 0.1);">
          <!-- Header -->
          <div class="sticky top-0 flex items-center justify-between p-6 border-b z-10" style="background: #1a1f2e; border-color: rgba(255, 255, 255, 0.1);">
            <h2 class="text-xl font-bold" style="color: #ffffff;">Create Task</h2>
            <button 
              @click="closeModal"
              class="close-btn w-8 h-8 flex items-center justify-center rounded-lg transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Error/Success Messages -->
          <div class="px-6 pt-4">
            <Transition name="fade">
              <div v-if="errorMessage" class="mb-4 p-4 border rounded-xl" style="background: rgba(244, 67, 54, 0.15); border-color: rgba(244, 67, 54, 0.4);">
                <p class="text-sm" style="color: rgb(255, 120, 117);">{{ errorMessage }}</p>
              </div>
            </Transition>
            <Transition name="fade">
              <div v-if="successMessage" class="mb-4 p-4 border rounded-xl" style="background: rgba(76, 175, 80, 0.15); border-color: rgba(76, 175, 80, 0.4);">
                <p class="text-sm" style="color: rgb(129, 199, 132);">{{ successMessage }}</p>
              </div>
            </Transition>
          </div>

          <!-- Form -->
          <form @submit.prevent="submitTask" class="p-6 pt-2 space-y-5">
            <!-- Title -->
            <div>
              <label class="block text-sm font-medium mb-2" style="color: rgba(255, 255, 255, 0.9);">Task Title *</label>
              <input 
                v-model="form.title"
                type="text" 
                class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all"
                style="background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.1); color: #ffffff;"
                placeholder="Enter task title"
                required
              >
            </div>

            <!-- Category -->
            <div>
              <label class="block text-sm font-medium mb-2" style="color: rgba(255, 255, 255, 0.9);">Category *</label>
              <div class="relative">
                <select 
                  v-model="form.category"
                  required
                  class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 appearance-none cursor-pointer transition-all"
                  style="background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.1); color: #ffffff;"
                >
                  <option value="" disabled style="background: #1a1f2e; color: rgba(255, 255, 255, 0.5);">Select a category</option>
                  <option value="PERSONAL" style="background: #1a1f2e; color: #ffffff;">Personal</option>
                  <option value="SCHOOL" style="background: #1a1f2e; color: #ffffff;">School</option>
                  <option value="WORK" style="background: #1a1f2e; color: #ffffff;">Work</option>
                </select>
                <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 pointer-events-none" style="color: rgba(255, 255, 255, 0.5);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </div>
            </div>

            <!-- Due Date -->
            <div>
              <label class="block text-sm font-medium mb-2" style="color: rgba(255, 255, 255, 0.9);">Due Date</label>
              <input 
                v-model="form.due_date"
                type="date" 
                class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all"
                style="background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.1); color: #ffffff;"
              >
            </div>

            <!-- Description -->
            <div>
              <label class="block text-sm font-medium mb-2" style="color: rgba(255, 255, 255, 0.9);">Description</label>
              <textarea 
                v-model="form.description"
                rows="3"
                class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 resize-none transition-all"
                style="background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.1); color: #ffffff;"
                placeholder="Enter task description"
              ></textarea>
            </div>

            <!-- Attachment -->
            <div>
              <label class="block text-sm font-medium mb-2" style="color: rgba(255, 255, 255, 0.9);">Attachment</label>
              
              <!-- Upload Option Buttons -->
              <div class="flex gap-3 mb-3">
                <button
                  type="button"
                  @click="triggerFileInput"
                  class="flex-1 flex items-center justify-center gap-2 px-4 py-3 border rounded-xl transition-all hover:bg-white/10"
                  style="background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.1); color: rgba(255, 255, 255, 0.8);"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                  </svg>
                  <span class="text-sm font-medium">Upload File</span>
                </button>
                
                <button
                  type="button"
                  @click="openGoogleDrivePicker"
                  :disabled="googlePickerLoading"
                  class="flex-1 flex items-center justify-center gap-2 px-4 py-3 border rounded-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed hover:bg-white/10"
                  style="background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.1); color: rgba(255, 255, 255, 0.8);"
                >
                  <svg v-if="!googlePickerLoading" class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                    <path d="M12.24 6.46L17.37 14.85L22.5 6.46H12.24Z" fill="#0066DA"/>
                    <path d="M7.13 17.54H17.37L12.24 9.15L7.13 17.54Z" fill="#00AC47"/>
                    <path d="M1.5 6.46L6.62 14.85H16.86L11.74 6.46H1.5Z" fill="#EA4335"/>
                    <path d="M7.13 17.54L12.24 9.15L7.13 0.77L1.5 9.15L7.13 17.54Z" fill="#FFBA00"/>
                  </svg>
                  <svg v-else class="w-5 h-5 animate-spin" style="color: rgba(255, 255, 255, 0.7);" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span class="text-sm font-medium">Google Drive</span>
                </button>
              </div>
              
              <input 
                ref="fileInputRef"
                type="file" 
                class="hidden"
                @change="handleFileSelect"
              >
              
              <!-- Drop Zone / Selected File Display -->
              <div 
                class="w-full px-4 py-5 border border-dashed rounded-xl text-center cursor-pointer transition-colors hover:bg-white/5"
                style="background: rgba(255, 255, 255, 0.03); border-color: rgba(255, 255, 255, 0.15);"
                @click="handleDropZoneClick"
                @dragover.prevent
                @drop.prevent="handleFileDrop"
              >
                <div v-if="!selectedFile && !googleDriveFile" style="color: rgba(255, 255, 255, 0.4);">
                  <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                  <p class="text-sm">Drag and drop a file here</p>
                </div>
                
                <div v-else class="flex items-center justify-center gap-3">
                  <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center">
                    <svg v-if="googleDriveFile" class="w-5 h-5 text-blue-500" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M12.24 6.46L17.37 14.85L22.5 6.46H12.24Z"/>
                      <path d="M7.13 17.54H17.37L12.24 9.15L7.13 17.54Z"/>
                      <path d="M1.5 6.46L6.62 14.85H16.86L11.74 6.46H1.5Z"/>
                    </svg>
                    <svg v-else class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                  </div>
                  <div class="text-left flex-1">
                    <p class="text-sm font-medium" style="color: #60a5fa;">{{ selectedFile?.name || googleDriveFile?.name }}</p>
                    <p class="text-xs" style="color: rgba(255, 255, 255, 0.5);">{{ googleDriveFile ? 'From Google Drive' : 'From Local file' }}</p>
                  </div>
                </div>
              </div>
              
              <button 
                v-if="selectedFile || googleDriveFile"
                type="button"
                @click="clearSelectedFile"
                class="mt-2 text-xs text-red-500 hover:text-red-600"
              >
                Remove file
              </button>
            </div>

            <!-- Subtasks Section -->
            <div class="border rounded-xl p-5" style="background: rgba(255, 255, 255, 0.03); border-color: rgba(255, 255, 255, 0.1);">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold" style="color: #ffffff;">Subtasks</h3>
                <button 
                  type="button"
                  @click="addSubtask"
                  class="flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium transition-colors"
                  style="background: rgb(255,255,255); color: #000000;"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                  </svg>
                  Add
                </button>
              </div>
              
              <div v-if="subtasks.length === 0" class="text-center py-6 text-sm" style="color: rgba(255, 255, 255, 0.4);">
                No subtasks added yet
              </div>
              
              <div v-else class="space-y-2">
                <div 
                  v-for="(subtask, index) in subtasks" 
                  :key="subtask.id"
                  class="flex items-center gap-2 p-3 rounded-lg"
                  style="background: rgba(255, 255, 255, 0.05);"
                >
                  <input 
                    v-model="subtask.title"
                    type="text"
                    class="flex-1 px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                    style="background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.1); color: #ffffff;"
                    placeholder="Subtask title"
                  >
                  <button 
                    type="button"
                    @click="openSubtaskModal(index)"
                    class="p-2 rounded-lg transition-colors hover:bg-white/10"
                    style="color: rgba(255, 255, 255, 0.5);"
                    title="Edit subtask details"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </button>
                  <button 
                    type="button"
                    @click="removeSubtask(index)"
                    class="p-2 rounded-lg transition-colors text-red-500 hover:bg-red-500/10"
                    title="Remove subtask"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Collaborators Section -->
            <div class="border rounded-xl p-5" style="background: rgba(255, 255, 255, 0.03); border-color: rgba(255, 255, 255, 0.1);">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold" style="color: #ffffff;">Collaborators</h3>
                <button 
                  type="button"
                  @click="addCollaborator"
                  class="flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium transition-colors"
                  style="background: rgb(255, 255, 255); color: #000000;"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                  </svg>
                  Add
                </button>
              </div>
              
              <div v-if="collaborators.length === 0" class="text-center py-6 text-sm" style="color: rgba(255, 255, 255, 0.4);">
                No collaborators added yet
              </div>
              
              <div v-else class="space-y-2">
                <div 
                  v-for="(collab, index) in collaborators" 
                  :key="collab.id"
                  class="flex items-center gap-2 p-3 rounded-lg"
                  style="background: rgba(255, 255, 255, 0.05);"
                >
                  <div class="flex-1 relative">
                    <input 
                      v-model="collab.email"
                      type="email"
                      class="w-full px-3 py-2 pr-10 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                      :style="getCollaboratorInputStyle(collab)"
                      placeholder="Enter collaborator email"
                      @input="debouncedValidateCollaborator(collab)"
                    >
                    <div class="absolute right-3 top-1/2 -translate-y-1/2">
                      <svg v-if="collab.isValidating" class="w-4 h-4 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      <svg v-else-if="collab.isValid === true" class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                      </svg>
                      <svg v-else-if="collab.isValid === false" class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                      </svg>
                    </div>
                  </div>
                  <button 
                    type="button"
                    @click="removeCollaborator(index)"
                    class="p-2 rounded-lg transition-colors text-red-500 hover:bg-red-500/10"
                    title="Remove collaborator"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Submit Button -->
            <button 
              type="submit"
              :disabled="loading"
              class="w-full py-3.5 px-4 font-semibold rounded-lg shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
              style="background: rgb(255, 255, 255); color: #000000;"
            >
              <svg v-if="loading" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ loading ? 'Creating...' : 'Create Task' }}</span>
            </button>
          </form>
        </div>
      </div>
    </Transition>

    <!-- Subtask Edit Modal -->
    <Transition name="modal">
      <div 
        v-if="editingSubtaskIndex !== null"
        class="fixed inset-0 z-[60] flex items-center justify-center p-4"
        @click.self="closeSubtaskModal"
      >
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeSubtaskModal"></div>
        <div class="relative w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden" style="background: #1a1f2e; border: 1px solid rgba(255, 255, 255, 0.1);">
          <div class="flex items-center justify-between p-6 border-b" style="border-color: rgba(255, 255, 255, 0.1);">
            <h3 class="text-lg font-semibold" style="color: #ffffff;">Edit Subtask</h3>
            <button 
              @click="closeSubtaskModal"
              class="close-btn w-8 h-8 flex items-center justify-center rounded-lg transition-colors"
              style="color: rgba(255, 255, 255, 0.5);"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          
          <div class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium mb-2" style="color: rgba(255, 255, 255, 0.9);">Title</label>
              <input 
                v-model="editingSubtask.title"
                type="text"
                class="w-full px-4 py-3 border rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                style="background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.1); color: #ffffff;"
                placeholder="Subtask title"
              >
            </div>
            
            <div>
              <label class="block text-sm font-medium mb-2" style="color: rgba(255, 255, 255, 0.9);">Description</label>
              <textarea 
                v-model="editingSubtask.description"
                rows="3"
                class="w-full px-4 py-3 border rounded-xl resize-none focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                style="background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.1); color: #ffffff;"
                placeholder="Subtask description"
              ></textarea>
            </div>
            
            <div>
              <label class="block text-sm font-medium mb-2" style="color: rgba(255, 255, 255, 0.9);">Assign Collaborators</label>
              <div class="space-y-2 max-h-40 overflow-y-auto">
                <label 
                  class="flex items-center gap-3 p-3 rounded-lg cursor-pointer transition-colors hover:bg-white/10"
                  style="background: rgba(255, 255, 255, 0.05);"
                >
                  <input 
                    type="checkbox"
                    :value="authStore.currentUser?.email"
                    v-model="editingSubtask.collaborators"
                    class="w-4 h-4 rounded border-gray-300 text-blue-500 focus:ring-2 focus:ring-blue-500/50"
                  >
                  <span class="text-sm" style="color: #ffffff;">Me</span>
                  <span class="text-xs" style="color: rgba(255, 255, 255, 0.5);">({{ authStore.currentUser?.email }})</span>
                </label>
                <label 
                  v-for="collab in validCollaborators" 
                  :key="collab.id"
                  class="flex items-center gap-3 p-3 rounded-lg cursor-pointer transition-colors hover:bg-white/10"
                  style="background: rgba(255, 255, 255, 0.05);"
                >
                  <input 
                    type="checkbox"
                    :value="collab.email"
                    v-model="editingSubtask.collaborators"
                    class="w-4 h-4 rounded border-gray-300 text-blue-500 focus:ring-2 focus:ring-blue-500/50"
                  >
                  <span class="text-sm" style="color: #ffffff;">{{ collab.email }}</span>
                </label>
                <p v-if="validCollaborators.length === 0" class="text-sm text-center py-2" style="color: rgba(255, 255, 255, 0.4);">
                  Add collaborators to the task to assign them to subtasks
                </p>
              </div>
            </div>
          </div>
          
          <div class="flex items-center justify-end gap-3 p-6 border-t" style="border-color: rgba(255, 255, 255, 0.1);">
            <button 
              type="button"
              @click="closeSubtaskModal"
              class="px-4 py-2 transition-colors text-sm font-medium rounded-lg hover:bg-white/10"
              style="color: rgba(255, 255, 255, 0.7);"
            >
              Cancel
            </button>
            <button 
              type="button"
              @click="saveSubtaskEdit"
              class="px-6 py-2 rounded-lg transition-colors text-sm font-medium"
              style="background: rgb(255, 255, 255); color: #000000;"
            >
              Save
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
const props = defineProps<{
  isOpen: boolean
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'created'): void
}>()

const config = useRuntimeConfig()
const authStore = useAuthStore()
const { checkUser } = useApi()
const toast = useToast()

// Google Drive Picker
interface GoogleDriveFile {
  id: string
  name: string
  mimeType: string
  url: string
}

const googleDriveFile = ref<GoogleDriveFile | null>(null)
const googlePickerLoading = ref(false)

const { openPicker, accessToken: driveAccessToken } = useGooglePicker({
  onSelect: (file) => {
    googleDriveFile.value = file
    selectedFile.value = null
    googlePickerLoading.value = false
    toast.success('File selected from Google Drive')
  },
  onCancel: () => {
    googlePickerLoading.value = false
  },
  onError: (error) => {
    googlePickerLoading.value = false
    const errorMsg = error.message || ''
    if (!errorMsg.includes('cancelled') && !errorMsg.includes('popup_closed')) {
      toast.error('Failed to open Google Drive: ' + errorMsg)
    }
  },
})

const openGoogleDrivePicker = async () => {
  googlePickerLoading.value = true
  try {
    await openPicker()
  } catch {
    googlePickerLoading.value = false
  }
}

// Form State
const loading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const selectedFile = ref<File | null>(null)
const fileInputRef = ref<HTMLInputElement | null>(null)

interface Subtask {
  id: string
  title: string
  description: string
  collaborators: string[]
}

interface Collaborator {
  id: string
  email: string
  isValid: boolean | null
  isValidating: boolean
}

const form = reactive({
  title: '',
  description: '',
  category: '',
  due_date: '',
  status: 'pending',
})

const subtasks = ref<Subtask[]>([])
const collaborators = ref<Collaborator[]>([])

// Subtask modal state
const editingSubtaskIndex = ref<number | null>(null)
const editingSubtask = reactive({
  title: '',
  description: '',
  collaborators: [] as string[],
})

const validCollaborators = computed(() => 
  collaborators.value.filter(c => c.isValid === true && c.email.trim())
)

const generateId = () => Math.random().toString(36).substr(2, 9)

// Methods
const closeModal = () => {
  emit('close')
}

const resetForm = () => {
  form.title = ''
  form.description = ''
  form.category = ''
  form.due_date = ''
  form.status = 'pending'
  subtasks.value = []
  collaborators.value = []
  selectedFile.value = null
  googleDriveFile.value = null
  errorMessage.value = ''
  successMessage.value = ''
}

const triggerFileInput = () => {
  fileInputRef.value?.click()
}

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files?.length) {
    selectedFile.value = target.files[0]
    googleDriveFile.value = null
  }
}

const handleFileDrop = (event: DragEvent) => {
  if (event.dataTransfer?.files?.length) {
    selectedFile.value = event.dataTransfer.files[0]
    googleDriveFile.value = null
  }
}

const handleDropZoneClick = () => {
  if (!selectedFile.value && !googleDriveFile.value) {
    triggerFileInput()
  }
}

const clearSelectedFile = () => {
  selectedFile.value = null
  googleDriveFile.value = null
}

const addSubtask = () => {
  subtasks.value.push({
    id: generateId(),
    title: '',
    description: '',
    collaborators: [],
  })
}

const removeSubtask = (index: number) => {
  subtasks.value.splice(index, 1)
}

const openSubtaskModal = (index: number) => {
  editingSubtaskIndex.value = index
  const subtask = subtasks.value[index]
  editingSubtask.title = subtask.title
  editingSubtask.description = subtask.description
  editingSubtask.collaborators = [...subtask.collaborators]
}

const closeSubtaskModal = () => {
  editingSubtaskIndex.value = null
}

const saveSubtaskEdit = () => {
  if (editingSubtaskIndex.value !== null) {
    const subtask = subtasks.value[editingSubtaskIndex.value]
    subtask.title = editingSubtask.title
    subtask.description = editingSubtask.description
    subtask.collaborators = [...editingSubtask.collaborators]
  }
  closeSubtaskModal()
}

const addCollaborator = () => {
  collaborators.value.push({
    id: generateId(),
    email: '',
    isValid: null,
    isValidating: false,
  })
}

const removeCollaborator = (index: number) => {
  collaborators.value.splice(index, 1)
}

// Debounced validation
let validationTimeouts: Record<string, ReturnType<typeof setTimeout>> = {}

const debouncedValidateCollaborator = (collab: Collaborator) => {
  if (validationTimeouts[collab.id]) {
    clearTimeout(validationTimeouts[collab.id])
  }
  
  collab.isValid = null
  collab.isValidating = false
  
  const email = collab.email.trim()
  if (!email || !email.includes('@')) return
  
  collab.isValidating = true
  
  validationTimeouts[collab.id] = setTimeout(async () => {
    try {
      const result = await checkUser(email)
      collab.isValid = result?.exists === true
    } catch {
      collab.isValid = false
    } finally {
      collab.isValidating = false
    }
  }, 500)
}

const getCollaboratorInputStyle = (collab: Collaborator) => {
  let borderColor = 'rgba(255, 255, 255, 0.1)'
  if (collab.isValid === true) borderColor = 'rgba(34, 197, 94, 0.5)'
  if (collab.isValid === false) borderColor = 'rgba(239, 68, 68, 0.5)'
  return {
    background: 'rgba(255, 255, 255, 0.05)',
    borderColor,
    color: '#ffffff'
  }
}

const submitTask = async () => {
  if (!form.category) {
    errorMessage.value = 'Please select a category.'
    return
  }
  
  const invalidCollabs = collaborators.value.filter(c => 
    c.email.trim() && c.email.includes('@') && (c.isValid === false || c.isValidating)
  )
  
  if (invalidCollabs.length > 0) {
    errorMessage.value = 'Please add valid emails for all collaborators.'
    return
  }
  
  loading.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const formData = new FormData()
    formData.append('title', form.title)
    formData.append('description', form.description || '')
    formData.append('category', form.category)
    if (form.due_date) formData.append('due_date', form.due_date)
    formData.append('status', 'pending')
    
    const subtasksData = subtasks.value
      .filter(s => s.title.trim())
      .map(s => ({
        title: s.title.trim(),
        status: 'pending',
        description: s.description || '',
        collaborators: s.collaborators || [],
      }))
    
    if (subtasksData.length > 0) {
      formData.append('subtasks', JSON.stringify(subtasksData))
    }
    
    const collaboratorsData = collaborators.value
      .filter(c => c.email.trim() && c.isValid === true)
      .map(c => c.email.trim())
    
    if (collaboratorsData.length > 0) {
      formData.append('collaborators', JSON.stringify(collaboratorsData))
    }
    
    if (selectedFile.value) {
      formData.append('attachment', selectedFile.value)
    }
    
    if (googleDriveFile.value) {
      formData.append('google_drive_file', JSON.stringify({
        id: googleDriveFile.value.id,
        name: googleDriveFile.value.name,
        mimeType: googleDriveFile.value.mimeType,
        accessToken: driveAccessToken.value,
      }))
    }
    
    const response = await fetch(`${config.public.apiBase}/tasks`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
      },
      body: formData,
    })
    
    if (!response.ok) {
      const data = await response.json()
      throw new Error(data.message || 'Failed to create task')
    }
    
    successMessage.value = 'Task created successfully!'
    toast.success('Task created successfully!')
    
    // Reset form and close modal after delay
    setTimeout(() => {
      resetForm()
      emit('created')
      emit('close')
    }, 1000)
  } catch (error: any) {
    errorMessage.value = error?.message || 'Failed to create task. Please try again.'
    toast.error(error?.message || 'Failed to create task')
  } finally {
    loading.value = false
  }
}

// Watch for modal close to reset form
watch(() => props.isOpen, (newValue) => {
  if (!newValue) {
    // Small delay before reset to allow close animation
    setTimeout(resetForm, 300)
  }
})
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-active > div:last-child,
.modal-leave-active > div:last-child {
  transition: transform 0.3s ease, opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-from > div:last-child,
.modal-leave-to > div:last-child {
  transform: scale(0.95) translateY(10px);
  opacity: 0;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Custom checkbox styling */
input[type="checkbox"] {
  appearance: none;
  -webkit-appearance: none;
  width: 1rem;
  height: 1rem;
  border: 2px solid rgba(255, 255, 255, 0.2);
  border-radius: 0.25rem;
  background: rgba(255, 255, 255, 0.05);
  cursor: pointer;
  position: relative;
}

input[type="checkbox"]:checked {
  background: #3b82f6;
  border-color: #3b82f6;
}

input[type="checkbox"]:checked::after {
  content: '';
  position: absolute;
  left: 3px;
  top: 0px;
  width: 4px;
  height: 8px;
  border: solid white;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

/* Close button hover styling */
.close-btn {
  color: rgba(255, 255, 255, 0.5);
  background: transparent;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.1);
}
</style>
