<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

import { ref, watch } from 'vue';

const isOpen = ref(false);

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    error: String,
    description: String
});

const emit = defineEmits(['update:show']);

watch(() => props.show, (newValue) => {
    isOpen.value = newValue;
}, { immediate: true });

const close = () => {
    isOpen.value = false;
    emit('update:show', false);
}

</script>

<template>
  <Dialog v-model:open="isOpen" class="dialog">
    <DialogContent>
      <DialogHeader onClose="close">
        <DialogTitle>{{ error }}</DialogTitle>
        <DialogDescription>
          {{ description }}
        </DialogDescription>
      </DialogHeader>

      <DialogFooter>
        <Button type="button" @click="close">OK</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>