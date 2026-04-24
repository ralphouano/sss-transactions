<template>
  <div class="signature-pad-container rounded-lg border border-blue-200 bg-white p-4">
    <div class="mb-3 flex items-center justify-between gap-3">
      <label v-if="label" class="text-base font-medium text-blue-950">{{ label }}</label>
      <div class="flex items-center gap-2">
        <button
          type="button"
          @click="undo"
          class="rounded-md border border-blue-200 bg-white px-3 py-2 text-sm font-medium text-blue-900 transition hover:bg-blue-50"
        >
          Undo
        </button>
        <button
          type="button"
          @click="clear"
          class="rounded-md bg-red-500 px-3 py-2 text-sm font-medium text-white transition hover:bg-red-600"
        >
          Clear
        </button>
      </div>
    </div>
    <div ref="canvasViewport" class="w-full">
      <div class="mx-auto w-fit rounded-lg border-2 border-dashed border-blue-400 bg-blue-50/40 p-1 shadow-inner">
        <canvas
          ref="canvas"
          class="block touch-none rounded-md bg-white"
        ></canvas>
      </div>
    </div>
    <p class="mt-2 text-sm text-gray-700">Please sign inside the box. Use Undo for the last stroke or Clear to start over.</p>
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
  height: 260
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const canvas = ref<HTMLCanvasElement | null>(null)
const canvasViewport = ref<HTMLDivElement | null>(null)
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
  const viewportWidth = canvasViewport.value?.clientWidth ?? props.width
  const targetWidth = Math.min(Math.max(viewportWidth - 8, 320), props.width)

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
      penColor: 'rgb(0, 0, 0)',
      minWidth: 2,
      maxWidth: 4.5,
      throttle: 8,
      velocityFilterWeight: 0.6,
    })
    resizeCanvas()
    window.addEventListener('resize', resizeCanvas)

    signaturePad.addEventListener('beginStroke', () => {
      errorMessage.value = ''
    })

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

const undo = () => {
  if (!signaturePad) return
  const data = signaturePad.toData()
  if (!data.length) return
  data.pop()
  signaturePad.fromData(data)
  emit('update:modelValue', signaturePad.isEmpty() ? '' : signaturePad.toDataURL('image/png'))
}
</script>

<style scoped>
.signature-pad-container canvas {
  cursor: crosshair;
}
</style>
