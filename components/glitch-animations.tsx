"use client"

import { motion } from "framer-motion"
import { ReactNode, useState } from "react"

interface GlitchTextProps {
  children: ReactNode
  className?: string
  delay?: number
  style?: React.CSSProperties
}

export function GlitchText({ children, className = "", delay = 0, style = {} }: GlitchTextProps) {
  return (
    <motion.h2
      className={`relative ${className}`}
      whileHover={{
        textShadow: [
          "0 0 0 transparent",
          "3px 0 0 rgba(255,0,0,0.5), -3px 0 0 rgba(0,255,255,0.5)",
          "-3px 0 0 rgba(255,0,0,0.5), 3px 0 0 rgba(0,255,255,0.5)",
          "2px 0 0 rgba(255,0,0,0.4), -2px 0 0 rgba(0,255,255,0.4)",
          "-2px 0 0 rgba(255,0,0,0.4), 2px 0 0 rgba(0,255,255,0.4)",
          "0 0 0 transparent"
        ],
        transition: { 
          duration: 0.5,
          times: [0, 0.2, 0.4, 0.6, 0.8, 1],
          ease: "linear",
          repeat: Infinity
        }
      }}
      style={style}
    >
      {children}
    </motion.h2>
  )
}

interface GlitchContainerProps {
  children: ReactNode
  className?: string
  delay?: number
}

export function GlitchContainer({ children, className = "", delay = 0 }: GlitchContainerProps) {
  return (
    <motion.div
      className={`relative ${className}`}
      initial={{ 
        opacity: 0,
        x: -15,
        filter: "contrast(1.5) brightness(1.2)"
      }}
      animate={{ 
        opacity: 1,
        x: [0, -6, 6, -4, 4, -2, 2, 0],
        filter: [
          "contrast(1.5) brightness(1.2)",
          "contrast(1.3) brightness(1.1) hue-rotate(5deg)",
          "contrast(1.2) brightness(1.05) hue-rotate(-5deg)",
          "contrast(1.1) brightness(1.02)",
          "contrast(1) brightness(1) hue-rotate(0deg)"
        ]
      }}
      transition={{ 
        duration: 0.5, 
        delay,
        ease: "easeOut",
        times: [0, 0.2, 0.4, 0.6, 1]
      }}
    >
      {children}
    </motion.div>
  )
}

interface GlitchButtonProps {
  children: ReactNode
  className?: string
  onClick?: () => void
  delay?: number
}

export function GlitchButton({ children, className = "", onClick, delay = 0 }: GlitchButtonProps) {
  return (
    <motion.button
      className={`relative overflow-hidden ${className}`}
      initial={{ 
        opacity: 0, 
        scale: 0.9,
        x: -10,
        boxShadow: "3px 0 0 rgba(255,0,0,0.5), -3px 0 0 rgba(0,255,255,0.5)"
      }}
      animate={{ 
        opacity: 1, 
        scale: 1,
        x: [0, -4, 4, -2, 2, 0],
        boxShadow: [
          "3px 0 0 rgba(255,0,0,0.5), -3px 0 0 rgba(0,255,255,0.5)",
          "-3px 0 0 rgba(255,0,0,0.4), 3px 0 0 rgba(0,255,255,0.4)",
          "2px 0 0 rgba(255,0,0,0.3), -2px 0 0 rgba(0,255,255,0.3)",
          "-1px 0 0 rgba(255,0,0,0.2), 1px 0 0 rgba(0,255,255,0.2)",
          "0 0 20px rgba(0,0,0,0.1)"
        ]
      }}
      transition={{ 
        duration: 0.5, 
        delay,
        ease: "easeOut",
        times: [0, 0.2, 0.4, 0.6, 1]
      }}
      whileHover={{
        scale: 1.05,
        transition: { duration: 0.2 }
      }}
      whileTap={{
        scale: 0.98,
        transition: { duration: 0.1 }
      }}
      onClick={onClick}
    >
      <span className="relative z-10">{children}</span>
    </motion.button>
  )
}

interface GlitchImageProps {
  src: string
  alt: string
  className?: string
  delay?: number
}

export function GlitchImage({ src, alt, className = "", delay = 0 }: GlitchImageProps) {
  return (
    <motion.div
      className={`relative overflow-hidden ${className}`}
      initial={{ opacity: 0, scale: 0.95 }}
      animate={{ opacity: 1, scale: 1 }}
      transition={{ 
        duration: 0.7, 
        delay,
        ease: "easeOut"
      }}
      whileHover={{
        scale: 1.03,
        filter: "brightness(1.1) contrast(1.05)",
        transition: { duration: 0.3 }
      }}
    >
      <motion.img
        src={src}
        alt={alt}
        className="w-full h-full object-cover"
        whileHover={{
          x: [0, -1, 1, 0],
          y: [0, -1, 1, 0],
          transition: { 
            duration: 0.4,
            ease: "easeInOut"
          }
        }}
      />
      <motion.div
        className="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"
        initial={{ opacity: 0 }}
        whileHover={{ opacity: 1 }}
        transition={{ duration: 0.3 }}
      />
    </motion.div>
  )
}

// Utility per animazioni staggered
export const staggerContainer = {
  hidden: { opacity: 0 },
  show: {
    opacity: 1,
    transition: {
      staggerChildren: 0.1,
      delayChildren: 0.1
    }
  }
}

export const staggerItem = {
  hidden: { opacity: 0, y: 20, scale: 0.95 },
  show: { 
    opacity: 1, 
    y: 0, 
    scale: 1,
    transition: {
      duration: 0.6,
      ease: [0.25, 0.46, 0.45, 0.94]
    }
  }
}
