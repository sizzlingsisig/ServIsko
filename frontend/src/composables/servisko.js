import { definePreset } from '@primevue/themes'
import Aura from '@primevue/themes/aura'

const CustomAuraPreset = definePreset(Aura, {
  semantic: {
    primary: {
      50: '#F4E6E8',
      100: '#E5BDC2',
      200: '#D2939A',
      300: '#BF6A72',
      400: '#B05058',
      500: '#670723', // Your main custom color
      600: '#5A061E',
      700: '#4B0519',
      800: '#3B0413',
      900: '#2C030E',
      950: '#190209',
    },
    text: {
      50: '#F9F9F9', // Very light gray (almost white)
      100: '#F1F1F1', // Light gray
      200: '#E3E3E3', // Lighter gray
      300: '#C7C7C7', // Light-medium gray
      400: '#9B9B9B', // Medium gray
      500: '#4B4B4B', // Your main text color
      600: '#3E3E3E', // Darker gray
      700: '#323232', // Dark gray
      800: '#252525', // Very dark gray
      900: '#1A1A1A', // Almost black
      950: '#0D0D0D', // Near black
    },
    colorScheme: {
      light: {
        primary: {
          color: '{primary.500}',
          contrastColor: '#ffffff',
          hoverColor: '{primary.600}',
          activeColor: '{primary.700}',
        },
        highlight: {
          background: '{primary.50}',
          focusBackground: '{primary.100}',
          color: '{primary.700}',
          focusColor: '{primary.800}',
        },
      },
      dark: {
        primary: {
          color: '{primary.400}',
          contrastColor: '{surface.900}',
          hoverColor: '{primary.300}',
          activeColor: '{primary.200}',
        },
        highlight: {
          background: 'color-mix(in srgb, {primary.400}, transparent 84%)',
          focusBackground: 'color-mix(in srgb, {primary.400}, transparent 76%)',
          color: 'rgba(255,255,255,.87)',
          focusColor: 'rgba(255,255,255,.87)',
        },
      },
    },
  },
})

export default CustomAuraPreset
