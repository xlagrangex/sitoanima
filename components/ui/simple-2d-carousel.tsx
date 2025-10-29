"use client"

import React, { useState, useRef, useEffect } from 'react';
import { motion } from 'framer-motion';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Lightbox } from './lightbox';
import type { GalleryItem } from './circular-gallery';

interface SimpleCarouselProps {
  items: GalleryItem[];
  className?: string;
  aspectRatio?: string;
}

export function Simple2DCarousel({ items, className = "", aspectRatio }: SimpleCarouselProps) {
  const [currentIndex, setCurrentIndex] = useState(0);
  const [lightboxOpen, setLightboxOpen] = useState(false);
  const [lightboxIndex, setLightboxIndex] = useState(0);
  const startXRef = useRef(0);
  const isDraggingRef = useRef(false);

  // Show 2.5 images at once
  const imagesToShow = 2.5;
  const slideWidth = 100 / imagesToShow; // Percentage width per slide

  const maxIndex = Math.max(0, items.length - imagesToShow);

  const next = () => {
    setCurrentIndex((prev) => (prev >= maxIndex ? 0 : prev + 1));
  };

  const prev = () => {
    setCurrentIndex((prev) => (prev <= 0 ? maxIndex : prev - 1));
  };

  const handleImageClick = (index: number) => {
    setLightboxIndex(index);
    setLightboxOpen(true);
  };

  // Touch handlers
  const handleTouchStart = (e: React.TouchEvent) => {
    isDraggingRef.current = true;
    startXRef.current = e.touches[0].clientX;
  };

  const handleTouchMove = (e: React.TouchEvent) => {
    if (!isDraggingRef.current) return;
    e.preventDefault();
  };

  const handleTouchEnd = (e: React.TouchEvent) => {
    if (!isDraggingRef.current) return;
    
    const endX = e.changedTouches[0].clientX;
    const diff = startXRef.current - endX;
    
    if (Math.abs(diff) > 50) {
      if (diff > 0) {
        next();
      } else {
        prev();
      }
    }
    
    isDraggingRef.current = false;
  };

  // Auto-play
  useEffect(() => {
    const interval = setInterval(() => {
      next();
    }, 5000);

    return () => clearInterval(interval);
  }, [currentIndex, maxIndex]);

  const translateX = -(currentIndex * slideWidth);

  return (
    <>
      <div 
        className={`relative w-full h-full ${className}`}
        onTouchStart={handleTouchStart}
        onTouchMove={handleTouchMove}
        onTouchEnd={handleTouchEnd}
      >
        {/* Carousel Container */}
        <div className="relative w-full h-full overflow-hidden rounded-lg">
          <motion.div
            className="flex h-full"
            animate={{
              x: `${translateX}%`
            }}
            transition={{ duration: 0.3, ease: "easeInOut" }}
            style={{
              width: `${(items.length / imagesToShow) * 100}%`
            }}
          >
            {items.map((item, index) => (
              <div
                key={index}
                className="flex-shrink-0 px-2"
                style={{ width: `${slideWidth}%` }}
              >
                <div 
                  className="relative w-full cursor-pointer group"
                  style={{ aspectRatio: '4/3' }}
                  onClick={() => handleImageClick(index)}
                >
                  <img
                    src={item.photo.url}
                    alt={item.photo.text || `Image ${index + 1}`}
                    className="w-full h-full object-cover rounded-lg transition-transform group-hover:scale-105"
                    style={{
                      objectPosition: item.photo.pos || 'center'
                    }}
                  />
                  {/* Text Overlay */}
                  {item.photo.text && (
                    <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-2 rounded-b-lg">
                      <p className="text-white text-xs md:text-sm font-medium line-clamp-1">
                        {item.photo.text}
                      </p>
                      {item.photo.by && (
                        <p className="text-white/70 text-xs mt-0.5">
                          {item.photo.by}
                        </p>
                      )}
                    </div>
                  )}
                </div>
              </div>
            ))}
          </motion.div>
        </div>

        {/* Navigation Buttons */}
        {items.length > imagesToShow && (
          <>
            <button
              onClick={prev}
              className="absolute left-2 top-1/2 -translate-y-1/2 z-10 bg-black/50 hover:bg-black/70 text-white p-2 rounded-full transition-all"
              aria-label="Previous images"
            >
              <ChevronLeft className="w-5 h-5 md:w-6 md:h-6" />
            </button>
            <button
              onClick={next}
              className="absolute right-2 top-1/2 -translate-y-1/2 z-10 bg-black/50 hover:bg-black/70 text-white p-2 rounded-full transition-all"
              aria-label="Next images"
            >
              <ChevronRight className="w-5 h-5 md:w-6 md:h-6" />
            </button>
          </>
        )}

        {/* Indicators */}
        <div className="absolute bottom-4 left-1/2 -translate-x-1/2 z-10 flex gap-2">
          {Array.from({ length: Math.max(1, Math.ceil(items.length / imagesToShow)) }).map((_, index) => {
            const pageIndex = Math.floor(index * imagesToShow);
            const isActive = Math.floor(currentIndex / imagesToShow) === index;
            
            return (
              <button
                key={index}
                onClick={() => setCurrentIndex(pageIndex)}
                className={`h-2 rounded-full transition-all ${
                  isActive
                    ? 'w-8 bg-white'
                    : 'w-2 bg-white/50 hover:bg-white/75'
                }`}
                aria-label={`Go to page ${index + 1}`}
              />
            );
          })}
        </div>
      </div>

      {/* Lightbox */}
      <Lightbox
        images={items}
        initialIndex={lightboxIndex}
        isOpen={lightboxOpen}
        onClose={() => setLightboxOpen(false)}
        hideText={false}
        aspectRatio={aspectRatio}
      />
    </>
  );
}