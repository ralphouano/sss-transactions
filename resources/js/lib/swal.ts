import Swal from 'sweetalert2'

export const toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 2500,
  timerProgressBar: true,
})

export const showSuccessToast = (title: string) =>
  toast.fire({
    icon: 'success',
    title,
  })

export const showErrorToast = (title: string) =>
  toast.fire({
    icon: 'error',
    title,
  })

export const confirmAction = (title: string, text: string) =>
  Swal.fire({
    title,
    text,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#003087',
    cancelButtonColor: '#6b7280',
    reverseButtons: true,
  })
