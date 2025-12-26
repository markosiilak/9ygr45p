import withNuxt from './.nuxt/eslint.config.mjs'

export default withNuxt({
  rules: {
    // Vue specific rules
    'vue/no-v-html': 'warn', // Allow v-html with proper sanitization
    'vue/multi-word-component-names': 'error',
    'vue/no-unused-vars': 'error',
    'vue/no-unused-components': 'warn',
    'vue/require-default-prop': 'warn',
    'vue/require-prop-types': 'error',
    'vue/no-mutating-props': 'error',
    'vue/no-v-text-v-html-on-component': 'error',
    'vue/no-reserved-component-names': 'error',
    'vue/component-tags-order': ['error', {
      order: ['script', 'template', 'style']
    }],
    'vue/html-self-closing': ['error', {
      html: {
        void: 'always',
        normal: 'never',
        component: 'always'
      }
    }],
    'vue/max-attributes-per-line': ['warn', {
      singleline: 3,
      multiline: 1
    }],

    // TypeScript rules
    '@typescript-eslint/no-explicit-any': 'error',
    '@typescript-eslint/no-unused-vars': ['error', {
      argsIgnorePattern: '^_',
      varsIgnorePattern: '^_'
    }],
    '@typescript-eslint/explicit-function-return-type': 'off',
    '@typescript-eslint/explicit-module-boundary-types': 'off',
    '@typescript-eslint/no-non-null-assertion': 'warn',
    '@typescript-eslint/consistent-type-imports': ['error', {
      prefer: 'type-imports'
    }],

    // General JavaScript/TypeScript rules
    'no-console': ['warn', {
      allow: ['error', 'warn']
    }],
    'no-debugger': 'error',
    'no-alert': 'warn',
    'no-var': 'error',
    'prefer-const': 'error',
    'prefer-arrow-callback': 'error',
    'prefer-template': 'error',
    'prefer-destructuring': ['warn', {
      object: true,
      array: false
    }],
    'no-nested-ternary': 'warn',
    'no-unneeded-ternary': 'error',
    'no-duplicate-imports': 'error',
    'eqeqeq': ['error', 'always', { null: 'ignore' }],
    'curly': ['error', 'all'],
    'object-shorthand': ['error', 'always'],
    'quote-props': ['error', 'as-needed'],
    'no-useless-concat': 'error',
    'no-useless-return': 'error',
    'no-else-return': 'error',

    // Code quality
    'complexity': ['warn', 15],
    'max-depth': ['warn', 4],
    'max-lines-per-function': ['warn', {
      max: 100,
      skipBlankLines: true,
      skipComments: true
    }],
    'max-params': ['warn', 4],

    // Best practices
    'no-return-await': 'error',
    'require-await': 'warn',
    'no-await-in-loop': 'warn',
    'no-async-promise-executor': 'error',
    'no-promise-executor-return': 'error'
  }
})
