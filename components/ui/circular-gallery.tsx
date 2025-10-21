import React, { useState, useEffect, useRef, HTMLAttributes } from 'react';
import { Lightbox } from './lightbox';

// A simple utility for conditional class names
const cn = (...classes: (string | undefined | null | false)[]) => {
  return classes.filter(Boolean).join(' ');
}

// Define the type for a single gallery item
export interface GalleryItem {
  common: string;
  binomial: string;
  photo: {
    url: string; 
    text: string;
    pos?: string;
    by: string;
  };
}

// Define the props for the CircularGallery component
interface CircularGalleryProps extends HTMLAttributes<HTMLDivElement> {
  items: GalleryItem[];
  /** Controls how far the items are from the center. */
  radius?: number;
  /** Controls the speed of auto-rotation when not scrolling. */
  autoRotateSpeed?: number;
  /** Show text overlay on cards. */
  showText?: boolean;
}

const CircularGallery = React.forwardRef<HTMLDivElement, CircularGalleryProps>(
  ({ items, className, radius = 600, autoRotateSpeed = 0.02, showText = false, ...props }, ref) => {
    const [rotation, setRotation] = useState(0);
    const [isScrolling, setIsScrolling] = useState(false);
    const [isDragging, setIsDragging] = useState(false);
    const [isHovering, setIsHovering] = useState(false);
    const [isTouching, setIsTouching] = useState(false);
    const [dragStart, setDragStart] = useState({ x: 0, y: 0, rotation: 0 });
    const [hasMoved, setHasMoved] = useState(false);
    const [lightboxOpen, setLightboxOpen] = useState(false);
    const [lightboxIndex, setLightboxIndex] = useState(0);
    const scrollTimeoutRef = useRef<NodeJS.Timeout | null>(null);
    const animationFrameRef = useRef<number | null>(null);

    // Effect to handle scroll-based rotation - disabled when touching
    useEffect(() => {
      const handleScroll = () => {
        if (isTouching || isDragging) return; // Don't scroll rotate while touching
        
        setIsScrolling(true);
        if (scrollTimeoutRef.current) {
          clearTimeout(scrollTimeoutRef.current);
        }

        const scrollableHeight = document.documentElement.scrollHeight - window.innerHeight;
        const scrollProgress = scrollableHeight > 0 ? window.scrollY / scrollableHeight : 0;
        const scrollRotation = scrollProgress * 360;
        setRotation(scrollRotation);

        scrollTimeoutRef.current = setTimeout(() => {
          setIsScrolling(false);
        }, 150);
      };

      window.addEventListener('scroll', handleScroll, { passive: true });
      return () => {
        window.removeEventListener('scroll', handleScroll);
        if (scrollTimeoutRef.current) {
          clearTimeout(scrollTimeoutRef.current);
        }
      };
    }, [isTouching, isDragging]);

    // Effect for auto-rotation when not scrolling, hovering, dragging or touching
    useEffect(() => {
      const autoRotate = () => {
        if (!isScrolling && !isHovering && !isDragging && !isTouching) {
          setRotation(prev => prev + autoRotateSpeed);
        }
        animationFrameRef.current = requestAnimationFrame(autoRotate);
      };

      animationFrameRef.current = requestAnimationFrame(autoRotate);

      return () => {
        if (animationFrameRef.current) {
          cancelAnimationFrame(animationFrameRef.current);
        }
      };
    }, [isScrolling, isHovering, isDragging, isTouching, autoRotateSpeed]);

    // Drag handlers
    const handleMouseDown = (e: React.MouseEvent) => {
      setIsDragging(true);
      setDragStart({ x: e.clientX, rotation });
      e.preventDefault();
    };

    const handleMouseMove = (e: React.MouseEvent) => {
      if (!isDragging) return;
      const deltaX = e.clientX - dragStart.x;
      const rotationDelta = deltaX * 0.2; // Reduced sensitivity for more controlled dragging
      setRotation(dragStart.rotation + rotationDelta);
    };

    const handleMouseUp = () => {
      setIsDragging(false);
    };

    const handleMouseLeave = () => {
      setIsDragging(false);
      setIsHovering(false);
    };

    const handleMouseEnter = () => {
      setIsHovering(true);
    };

    // Touch handlers for mobile
    const handleTouchStart = (e: React.TouchEvent) => {
      setIsTouching(true);
      setHasMoved(false);
      setDragStart({ 
        x: e.touches[0].clientX, 
        y: e.touches[0].clientY, 
        rotation 
      });
    };

    const handleTouchMove = (e: React.TouchEvent) => {
      if (!isTouching) return;
      
      const deltaX = e.touches[0].clientX - dragStart.x;
      const deltaY = Math.abs(e.touches[0].clientY - dragStart.y);
      
      // Check if user is dragging horizontally (carousel) or vertically (scroll)
      if (Math.abs(deltaX) > 5 && Math.abs(deltaX) > deltaY) {
        // It's a horizontal drag - prevent default to stop scroll
        if (!hasMoved) {
          setHasMoved(true);
          setIsDragging(true);
        }
        e.preventDefault();
        const rotationDelta = deltaX * 0.15;
        setRotation(dragStart.rotation + rotationDelta);
      }
      // If it's vertical movement, let the scroll happen naturally (don't preventDefault)
    };

    const handleTouchEnd = () => {
      setIsTouching(false);
      setIsDragging(false);
      setHasMoved(false);
    };

    const anglePerItem = 360 / items.length;

    const handleImageClick = (index: number) => {
      if (!isDragging) {
        setLightboxIndex(index);
        setLightboxOpen(true);
      }
    };

    // Navigation arrows for mobile
    const isMobile = typeof window !== 'undefined' && window.innerWidth < 768;
    
    const goNext = () => {
      setRotation(prev => prev + anglePerItem);
    };
    
    const goPrev = () => {
      setRotation(prev => prev - anglePerItem);
    };
    
    return (
      <>
      <div
        ref={ref}
        role="region"
        aria-label="Circular 3D Gallery"
        className={cn("relative w-full h-full flex items-center justify-center", isDragging ? "cursor-grabbing" : "cursor-grab", className)}
        style={{ perspective: '2000px', userSelect: 'none' }}
        onMouseDown={!isMobile ? handleMouseDown : undefined}
        onMouseMove={!isMobile ? handleMouseMove : undefined}
        onMouseUp={!isMobile ? handleMouseUp : undefined}
        onMouseLeave={!isMobile ? handleMouseLeave : undefined}
        onMouseEnter={!isMobile ? handleMouseEnter : undefined}
        {...props}
      >
        <div
          className="relative w-full h-full"
          style={{
            transform: `rotateY(${rotation}deg)`,
            transformStyle: 'preserve-3d',
            transition: isDragging ? 'none' : 'transform 0.1s ease-out',
          }}
        >
          {items.map((item, i) => {
            const itemAngle = i * anglePerItem;
            const totalRotation = rotation % 360;
            const relativeAngle = (itemAngle + totalRotation + 360) % 360;
            const normalizedAngle = Math.abs(relativeAngle > 180 ? 360 - relativeAngle : relativeAngle);
            const opacity = Math.max(0.3, 1 - (normalizedAngle / 180));

            return (
              <div
                key={item.photo.url} 
                role="group"
                aria-label={item.common}
                className="absolute w-[180px] h-[240px] md:w-[240px] md:h-[320px]"
                style={{
                  transform: `rotateY(${itemAngle}deg) translateZ(${radius}px)`,
                  left: '50%',
                  top: '50%',
                  marginLeft: '-90px',
                  marginTop: '-120px',
                  opacity: opacity,
                  transition: 'opacity 0.3s linear'
                }}
              >
                <div 
                  className={`relative w-full h-full rounded-lg shadow-2xl overflow-hidden group backdrop-blur-lg cursor-pointer transition-transform hover:scale-105 ${
                    showText 
                      ? 'border-2 border-black bg-card/70 dark:bg-card/30' 
                      : 'border border-border bg-card/70 dark:bg-card/30'
                  }`}
                  onClick={() => handleImageClick(i)}
                >
                  <img
                    src={item.photo.url}
                    alt={item.photo.text}
                    className="absolute inset-0 w-full h-full object-cover"
                    style={{ objectPosition: item.photo.pos || 'center' }}
                  />
                  {/* Text overlay at the bottom - only show if showText is true */}
                  {showText && (
                    <div className="absolute bottom-0 left-0 w-full p-3 bg-gradient-to-t from-black/80 to-transparent text-white">
                      <h2 className={`font-sequel font-black tracking-wider uppercase ${
                        item.common === 'Grossomoddo' 
                          ? 'text-xs md:text-sm' 
                          : 'text-base md:text-lg'
                      }`}>
                        {item.common}
                      </h2>
                      <em className="text-xs md:text-sm italic opacity-80">{item.binomial}</em>
                    </div>
                  )}
                </div>
              </div>
            );
          })}
        </div>
        
        {/* Navigation arrows - only on mobile */}
        {isMobile && (
          <>
            <button
              onClick={goPrev}
              className="absolute left-2 top-1/2 -translate-y-1/2 z-20 bg-white/20 backdrop-blur-md hover:bg-white/30 text-white rounded-full p-3 shadow-lg transition-all active:scale-95"
              aria-label="Previous"
            >
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <button
              onClick={goNext}
              className="absolute right-2 top-1/2 -translate-y-1/2 z-20 bg-white/20 backdrop-blur-md hover:bg-white/30 text-white rounded-full p-3 shadow-lg transition-all active:scale-95"
              aria-label="Next"
            >
              <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </>
        )}
      </div>

      <Lightbox
        images={items}
        initialIndex={lightboxIndex}
        isOpen={lightboxOpen}
        onClose={() => setLightboxOpen(false)}
      />
      </>
    );
  }
);

CircularGallery.displayName = 'CircularGallery';

export { CircularGallery };
