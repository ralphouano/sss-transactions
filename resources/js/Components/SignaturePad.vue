<template>
  <div class="signature-pad-container rounded-lg border border-blue-200 bg-white p-4">
    <div class="mb-3 flex items-center justify-between gap-3">
      <label v-if="label" class="text-base font-medium text-blue-950">{{ label }}</label>
      <button
        type="button"
        @click="clear"
        class="rounded-md bg-red-500 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-red-600"
      >
        Clear
      </button>
    </div>
    <div class="rounded-lg border-2 border-dashed border-blue-400 bg-blue-50/40 p-1 shadow-inner">
      <canvas
        ref="canvas"
        class="block w-full touch-none rounded-md bg-white"
        @mousedown="startDrawing"
        @mousemove="draw"
        @mouseup="stopDrawing"
        @mouseleave="stopDrawing"
        @touchstart.prevent="handleTouchStart"
        @touchmove.prevent="handleTouchMove"
        @touchend.prevent="stopDrawing"
      ></canvas>
    </div>
    <p class="mt-2 text-xs text-gray-600">Sign inside the bordered box above.</p>
    <p v-if="errorMessage" class="mt-1 text-sm text-red-600">{{ errorMessage }}</p>
  </div>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue'
import SignaturePad from 'signature_pad'

interface Props {
  modelValue?: string
  label?: string
  error?: string
  width?: number
  height?: number
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  label: 'Signature',
  error: '',
  width: 600,
  height: 200
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const canvas = ref<HTMLCanvasElement | null>(null)
let signaturePad: SignaturePad | null = null
const errorMessage = ref(props.error)

watch(() => props.error, (newError) => {
  errorMessage.value = newError
})

watch(
  () => props.modelValue,
  (newValue) => {
    if (!signaturePad) return

    if (!newValue) {
      signaturePad.clear()
      return
    }

    if (newValue !== signaturePad.toDataURL('image/png')) {
      signaturePad.fromDataURL(newValue)
    }
  },
)

const resizeCanvas = () => {
  if (!canvas.value || !signaturePad) return

  const ratio = Math.max(window.devicePixelRatio || 1, 1)
  const parentWidth = canvas.value.parentElement?.clientWidth ?? props.width
  const targetWidth = Math.min(parentWidth, props.width)

  const previousData = signaturePad.isEmpty() ? null : signaturePad.toData()

  canvas.value.width = targetWidth * ratio
  canvas.value.height = props.height * ratio
  canvas.value.style.width = `${targetWidth}px`
  canvas.value.style.height = `${props.height}px`

  const context = canvas.value.getContext('2d')
  if (context) context.scale(ratio, ratio)

  signaturePad.clear()
  if (previousData) signaturePad.fromData(previousData)
}

onMounted(() => {
  if (canvas.value) {
    signaturePad = new SignaturePad(canvas.value, {
      backgroundColor: 'rgb(255, 255, 255)',
      penColor: 'rgb(0, 0, 0)'
    })
    resizeCanvas()
    window.addEventListener('resize', resizeCanvas)

    signaturePad.addEventListener('endStroke', () => {
      if (signaturePad) {
        const dataURL = signaturePad.toDataURL('image/png')
        emit('update:modelValue', dataURL)
        errorMessage.value = ''
      }
    })

    if (props.modelValue) {
      signaturePad.fromDataURL(props.modelValue)
    }
  }
})

onUnmounted(() => {
  window.removeEventListener('resize', resizeCanvas)
})

const clear = () => {
  if (signaturePad) {
    signaturePad.clear()
    emit('update:modelValue', '')
  }
}

const startDrawing = () => {
  errorMessage.value = ''
}

const draw = () => {
}

const stopDrawing = () => {
}

const handleTouchStart = (e: TouchEvent) => {
  const touch = e.touches[0]
  const mouseEvent = new MouseEvent('mousedown', {
    clientX: touch.clientX,
    clientY: touch.clientY
  })
  canvas.value?.dispatchEvent(mouseEvent)
}

const handleTouchMove = (e: TouchEvent) => {
  const touch = e.touches[0]
  const mouseEvent = new MouseEvent('mousemove', {
    clientX: touch.clientX,
    clientY: touch.clientY
  })
  canvas.value?.dispatchEvent(mouseEvent)
}
</script>

<style scoped>
.signature-pad-container canvas {
  cursor: crosshair;
}
</style>
