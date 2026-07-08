<?php
/**
 * Inline CSS keyframes per le animazioni della pagina ordine.
 * Caricato sia in celebration che info (regole minimali, no overhead).
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<style>
@keyframes pn-pop { 0% { transform: scale(0); opacity: 0 } 60% { transform: scale(1.15); opacity: 1 } 100% { transform: scale(1); opacity: 1 } }
@keyframes pn-pop-check { 0% { transform: scale(0) rotate(-20deg); opacity: 0 } 100% { transform: scale(1) rotate(0); opacity: 1 } }
@keyframes pn-rise { from { transform: translateY(12px); opacity: 0 } to { transform: translateY(0); opacity: 1 } }
@keyframes pn-burst { 0% { transform: scale(0); opacity: .8 } 100% { transform: scale(2.2); opacity: 0 } }
.pn-anim-pop { animation: pn-pop .6s cubic-bezier(.34,1.56,.64,1) both }
.pn-anim-check { animation: pn-pop-check .5s cubic-bezier(.34,1.56,.64,1) .25s both }
.pn-anim-rise { animation: pn-rise .55s ease-out both }
.pn-anim-rise-1 { animation-delay: .15s }
.pn-anim-rise-2 { animation-delay: .25s }
.pn-anim-rise-3 { animation-delay: .35s }
.pn-anim-rise-4 { animation-delay: .45s }
.pn-burst::before, .pn-burst::after { content: ""; position: absolute; inset: 0; border-radius: 9999px; background: rgba(67,204,177,.35); animation: pn-burst 1.6s ease-out infinite; }
.pn-burst::after { animation-delay: .8s }
@media (prefers-reduced-motion: reduce) {
	.pn-anim-pop, .pn-anim-check, .pn-anim-rise, .pn-burst::before, .pn-burst::after { animation: none !important }
}
</style>
