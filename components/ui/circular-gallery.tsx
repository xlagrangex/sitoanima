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
    const [dragStart, setDragStart] = useState({ x: 0, rotation: 0 });
    const [lightboxOpen, setLightboxOpen] = useState(false);
    const [lightboxIndex, setLightboxIndex] = useState(0);
    const scrollTimeoutRef = useRef<NodeJS.Timeout | null>(null);
    const animationFrameRef = useRef<number | null>(null);

    // Effect to handle scroll-based rotation
    useEffect(() => {
      const handleScroll = () => {
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
    }, []);

    // Effect for auto-rotation when not scrolling, hovering, or dragging
    useEffect(() => {
      const autoRotate = () => {
        if (!isScrolling && !isHovering && !isDragging) {
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
    }, [isScrolling, isHovering, isDragging, autoRotateSpeed]);

    // Drag handlers
    const handleMouseDown = (e: React.MouseEvent) => {
      setIsDragging(true);
      setDragStart({ x: e.clientX, rotation });
      e.preventDefault();
    };

    const handleMouseMove = (e: React.MouseEvent) => {
      if (!isDragging) return;
      const deltaX = e.clientX - dragStart.x;
      const rotationDelta = deltaX * 0.3; // Reduced sensitivity for more controlled dragging
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
      setIsDragging(true);
      setDragStart({ x: e.touches[0].clientX, rotation });
    };

    const handleTouchMove = (e: React.TouchEvent) => {
      if (!isDragging) return;
      const deltaX = e.touches[0].clientX - dragStart.x;
      const rotationDelta = deltaX * 0.3; // Reduced sensitivity for more controlled dragging
      setRotation(dragStart.rotation + rotationDelta);
    };

    const handleTouchEnd = () => {
      setIsDragging(false);
    };

    const anglePerItem = 360 / items.length;

    const handleImageClick = (index: number) => {
      if (!isDragging) {
        setLightboxIndex(index);
        setLightboxOpen(true);
      }
    };
    
    return (
      <>
      <div
        ref={ref}
        role="region"
        aria-label="Circular 3D Gallery"
        className={cn("relative w-full h-full flex items-center justify-center", isDragging ? "cursor-grabbing" : "cursor-grab", className)}
        style={{ perspective: '2000px', userSelect: 'none' }}
        onMouseDown={handleMouseDown}
        onMouseMove={handleMouseMove}
        onMouseUp={handleMouseUp}
        onMouseLeave={handleMouseLeave}
        onMouseEnter={handleMouseEnter}
        onTouchStart={handleTouchStart}
        onTouchMove={handleTouchMove}
        onTouchEnd={handleTouchEnd}
        {...props}
      >
        <div
          className="relative w-full h-full"
          style={{
            transform: `rotateY(${rotation}deg)`,
            transformStyle: 'preserve-3d',
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
                  className="relative w-full h-full rounded-lg shadow-2xl overflow-hidden group border border-border bg-card/70 dark:bg-card/30 backdrop-blur-lg cursor-pointer transition-transform hover:scale-105"
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
