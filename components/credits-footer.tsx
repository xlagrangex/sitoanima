"use client"

import { useEffect } from 'react'
import Image from 'next/image'

export function CreditsFooter() {
  useEffect(() => {
    // Update year automatically (though we can do this in React directly)
    const currentYear = new Date().getFullYear()
    
    // Improve touch interactions on mobile
    if ('ontouchstart' in window) {
      document.querySelectorAll('.footer-credits a').forEach(link => {
        link.addEventListener('touchstart', function(this: HTMLElement) {
          this.style.transition = 'all 0.1s ease'
        })
        
        link.addEventListener('touchend', function(this: HTMLElement) {
          setTimeout(() => {
            this.style.transition = 'all 0.3s ease'
          }, 100)
        })
      })
    }
  }, [])

  const currentYear = new Date().getFullYear()

  return (
    <>
      <style jsx>{`
        /* Stili isolati solo per il footer - non intaccano il resto del sito */
        .footer-credits {
            /* Reset specifico per il footer */
            all: initial;
            
            /* Stili del footer */
            opacity: 0;
            animation: fadeInUp 0.8s ease-out 0.3s forwards;
            background: linear-gradient(180deg, #f8f9fa 0%, #f1f3f4 100%);
            border-top: 1px solid #e9ecef;
            padding: 8px 20px 10px 20px;
            text-align: center;
            width: 100vw;
            position: relative;
            display: block;
            box-sizing: border-box;
            font-family: 'Inter', Arial, sans-serif;
            margin-left: calc(-50vw + 50%);
        }
        
        /* Tutti gli elementi interni al footer */
        .footer-credits *,
        .footer-credits *::before,
        .footer-credits *::after {
            box-sizing: border-box;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .footer-credits .decorative-line {
            width: 50px;
            height: 2px;
            background: linear-gradient(90deg, #1a4088, #a51b7f, #da5712);
            margin: 0 auto 8px auto;
            border-radius: 1px;
            display: block;
        }

        /* Brand styling */
        .footer-credits .brand-biz {
            font-family: 'Inter', sans-serif;
            font-weight: 200;
            font-style: italic;
            text-transform: uppercase;
            margin-right: 1px;
            letter-spacing: -0.05em;
            display: inline;
        }
        
        .footer-credits .brand-studio {
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            margin-left: 0px;
            letter-spacing: -0.05em;
            display: inline;
        }
        
        .footer-credits .footer-content {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 8px;
        }
        
        .footer-credits .footer-text {
            font-size: 12px;
            color: #6c757d;
            font-weight: 400;
            font-family: 'Inter', sans-serif;
            line-height: 1.3;
            margin: 0;
            padding: 0;
            display: inline;
        }
        
        .footer-credits .footer-link {
            color: #000;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            border-radius: 6px;
            padding: 3px 8px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(45deg, transparent, transparent);
            border: 1px solid transparent;
            display: inline-flex;
            align-items: center;
        }
        
        .footer-credits .footer-link:hover {
            color: transparent;
            background: linear-gradient(135deg, #1a4088 0%, #a51b7f 50%, #da5712 100%);
            background-clip: text;
            -webkit-background-clip: text;
            border: 1px solid rgba(26, 64, 136, 0.2);
            box-shadow: 0 4px 15px rgba(26, 64, 136, 0.15), 0 0 20px rgba(165, 27, 127, 0.1);
            transform: translateY(-1px) scale(1.02);
        }
        
        .footer-credits .footer-link img {
            height: 22px;
            width: auto;
            margin-right: -3px;
            vertical-align: middle;
            transform: translateY(-1px);
            border: none;
            outline: none;
        }
        
        .footer-credits .made-in-italy {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(45deg, #fff, #f8f9fa);
            padding: 2px 6px;
            border-radius: 10px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            margin-left: 6px;
            transition: all 0.3s ease;
        }
        
        .footer-credits .footer-bottom {
            font-size: 10px;
            color: #6c757d;
            font-family: 'Inter', sans-serif;
            font-weight: 400;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            line-height: 1.3;
        }
        
        .footer-credits .footer-bottom a {
            color: #6c757d;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 9px;
            opacity: 0.8;
            padding: 2px 4px;
            border-radius: 8px;
            white-space: nowrap;
            font-family: 'Inter', sans-serif;
        }
        
        .footer-credits .footer-bottom span {
            font-family: 'Inter', sans-serif;
            color: inherit;
            margin: 0;
            padding: 0;
        }
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .footer-credits {
                padding: 6px 15px 8px 15px;
            }
            
            .footer-credits .footer-content {
                gap: 4px;
                text-align: center;
                margin-bottom: 6px;
                justify-content: center;
                align-items: center;
            }
            
            .footer-credits .footer-text {
                font-size: 11px;
            }
            
            .footer-credits .footer-link {
                font-size: 12px;
                padding: 2px 6px;
                margin: 1px 0;
            }
            
            .footer-credits .footer-link img {
                height: 20px;
            }
            
            .footer-credits .made-in-italy {
                margin-left: 4px;
                margin-top: 2px;
            }
            
            .footer-credits .decorative-line {
                width: 40px;
                margin-bottom: 6px;
            }
            
            .footer-credits .footer-bottom {
                font-size: 9px;
                gap: 4px;
                line-height: 1.2;
            }
            
            .footer-credits .footer-bottom a {
                font-size: 8px;
                padding: 2px 3px;
                margin: 0 1px;
            }
            
            .footer-credits .footer-links-row {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 4px;
                align-items: center;
            }
            
            .footer-credits .footer-meta-row {
                display: flex;
                align-items: center;
                gap: 4px;
                margin-top: 3px;
            }
        }
        
        /* Extra small screens */
        @media (max-width: 480px) {
            .footer-credits {
                padding: 4px 12px 6px 12px;
            }
            
            .footer-credits .footer-text {
                font-size: 10px;
            }
            
            .footer-credits .footer-link {
                font-size: 11px;
            }
            
            .footer-credits .footer-bottom {
                font-size: 8px;
            }
            
            .footer-credits .footer-bottom a {
                font-size: 7px;
                padding: 1px 2px;
            }
        }
        
        /* Touch-friendly hover effects for mobile */
        @media (hover: none) and (pointer: coarse) {
            .footer-credits .footer-link:active {
                color: transparent;
                background: linear-gradient(135deg, #1a4088 0%, #a51b7f 50%, #da5712 100%);
                background-clip: text;
                -webkit-background-clip: text;
                transform: scale(0.98);
            }
            
            .footer-credits .footer-bottom a:active {
                transform: scale(0.95);
                opacity: 1;
            }
        }
      `}</style>

      <div className="footer-credits">
        <div className="decorative-line"></div>
        
        <div className="footer-content">
          <span className="footer-text">
            Questo sito è stato realizzato con ❤️ da
          </span>
          
          <a 
            href="https://bizstudio.it" 
            target="_blank" 
            rel="noopener noreferrer"
            className="footer-link"
          >
            <img src="https://i.ibb.co/XkFvvj94/favicon.png" alt="favicon" />
            <span className="brand-biz">Biz</span><span className="brand-studio">Studio</span>
          </a>
          
          <span className="footer-text">
            di Vincenzo Petrone
          </span>
          
          <div className="made-in-italy">
            <span style={{ fontSize: '9px', color: '#28a745', fontWeight: 600, marginRight: '3px' }}>🇮🇹</span>
            <span style={{ fontSize: '9px', color: '#495057', fontWeight: 500 }}>Made in Italy</span>
          </div>
        </div>
        
        <div className="footer-bottom">
          <div className="footer-links-row">
            <a 
              href="https://wa.me/393792473044?text=Ciao!%20Ho%20visto%20un%20sito%20che%20avete%20realizzato%20e%20vorrei%20confrontarmi%20con%20voi%20per%20un%20mio%20progetto.." 
              target="_blank"
              rel="noopener noreferrer"
              onMouseOver={(e) => {
                e.currentTarget.style.color = '#25d366'
                e.currentTarget.style.opacity = '1'
                e.currentTarget.style.backgroundColor = 'rgba(37,211,102,0.1)'
                e.currentTarget.style.transform = 'scale(1.05)'
              }}
              onMouseOut={(e) => {
                e.currentTarget.style.color = '#6c757d'
                e.currentTarget.style.opacity = '0.8'
                e.currentTarget.style.backgroundColor = 'transparent'
                e.currentTarget.style.transform = 'scale(1)'
              }}
            >
              Hai bisogno di un sito web? Scrivici su WhatsApp
            </a>
            
            <span style={{ opacity: 0.4 }}>•</span>
            
            <a 
              href="https://bizstudio.it/portfolio" 
              target="_blank"
              rel="noopener noreferrer"
              onMouseOver={(e) => {
                e.currentTarget.style.color = '#a51b7f'
                e.currentTarget.style.opacity = '1'
                e.currentTarget.style.backgroundColor = 'rgba(165,27,127,0.1)'
                e.currentTarget.style.transform = 'scale(1.05)'
              }}
              onMouseOut={(e) => {
                e.currentTarget.style.color = '#6c757d'
                e.currentTarget.style.opacity = '0.8'
                e.currentTarget.style.backgroundColor = 'transparent'
                e.currentTarget.style.transform = 'scale(1)'
              }}
            >
              Vedi altri nostri lavori
            </a>
          </div>
          
          <div className="footer-meta-row">
            <span style={{ opacity: 0.6 }}>Doping per la tua Azienda</span>
            <span style={{ opacity: 0.4 }}>•</span>
            <span style={{ opacity: 0.7 }}>© {currentYear}</span>
          </div>
        </div>
      </div>
    </>
  )
}



