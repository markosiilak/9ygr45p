/**
 * Composable for managing common form state
 */

export const useFormState = () => {
  const loading = ref(false)
  const error = ref('')
  const submitting = ref(false)
  const success = ref(false)
  const errors = ref<Record<string, string>>({})

  /**
   * Reset all form state
   */
  const resetState = () => {
    loading.value = false
    error.value = ''
    submitting.value = false
    success.value = false
    errors.value = {}
  }

  /**
   * Set error message
   */
  const setError = (message: string) => {
    error.value = message
  }

  /**
   * Set field-specific error
   */
  const setFieldError = (field: string, message: string) => {
    errors.value[field] = message
  }

  /**
   * Clear all errors
   */
  const clearErrors = () => {
    error.value = ''
    errors.value = {}
  }

  /**
   * Set loading state
   */
  const setLoading = (value: boolean) => {
    loading.value = value
  }

  /**
   * Set submitting state
   */
  const setSubmitting = (value: boolean) => {
    submitting.value = value
  }

  /**
   * Set success state
   */
  const setSuccess = (value: boolean) => {
    success.value = value
  }

  return {
    loading,
    error,
    submitting,
    success,
    errors,
    resetState,
    setError,
    setFieldError,
    clearErrors,
    setLoading,
    setSubmitting,
    setSuccess
  }
}
