export const formatTransactionType = (value: string): string =>
  value
    .replace('daem_disbursement_account_enrollment_module', 'DAEM')
    .split('_')
    .map((word) => {
      const lower = word.toLowerCase()
      if (lower === 'mysss') return 'mySSS'
      if (lower === 'prn') return 'PRN'
      if (lower === 'daem') return 'DAEM'
      return word.charAt(0).toUpperCase() + word.slice(1)
    })
    .join(' ')

