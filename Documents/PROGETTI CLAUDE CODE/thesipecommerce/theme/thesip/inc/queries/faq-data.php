<?php
/**
 * Dataset FAQ — porting da lib/faq-data.ts del Next.
 *
 * @package Pharmanow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function pn_get_faq_data(): array {
	$data = array(
		array(
			'id'        => 'prodotti',
			'title'     => __( 'Le carte', 'pharmanow' ),
			'icon'      => 'package',
			'questions' => array(
				array(
					'q' => __( 'Cosa contiene il Set Completo di The SIP?', 'pharmanow' ),
					'a' => __( "Il Set Completo contiene 31 carte illustrate di biologia marina, ciascuna dedicata a una specie o a un fenomeno del mare.\n\n• Le carte sono organizzate in 3 temi che raccontano il mare da tre punti di vista diversi.\n\n• Ogni illustrazione è firmata da uno dei 25 artisti che hanno collaborato al progetto.\n\n• Sul retro trovi le informazioni scientifiche curate dai nostri due biologi marini, Manuel e Giuliano.\n\n• È inclusa anche la carta speciale Zoosparkle, la più amata del mazzo.\n\nÈ un piccolo museo del mare che puoi tenere in tasca: The Sea In your Pocket.", 'pharmanow' ),
				),
				array(
					'q' => __( 'In che lingua sono le carte, italiano o inglese?', 'pharmanow' ),
					'a' => __( "Le carte sono disponibili sia in italiano sia in inglese: scegli tu la lingua che preferisci.\n\nPer indicarci la tua scelta, scrivi semplicemente \"IT\" o \"EN\" nel campo note dell'ordine, durante il checkout.\n\nSe non specifichi nulla, prepariamo il set nella versione italiana.", 'pharmanow' ),
				),
				array(
					'q' => __( 'Cosa cambia tra Set Completo, Set + Stickers e i Bundle?', 'pharmanow' ),
					'a' => __( "Abbiamo pensato quattro modi per portarti il mare a casa:\n\n• Set Completo (€25): le 31 carte illustrate.\n\n• Set + Stickers (€30): le 31 carte più il pacchetto di stickers illustrati, con spedizione gratuita.\n\n• Double Bundle (€40): due Set Completi, perfetto per condividerne uno con chi ami o per il gioco a due.\n\n• Bundle Deluxe (€125): l'edizione più speciale, con una carta personalizzata realizzata su misura dai nostri artisti.\n\nPuoi confrontarli tutti nella pagina del catalogo.", 'pharmanow' ),
				),
				array(
					'q' => __( "Come funziona la carta personalizzata del Bundle Deluxe?", 'pharmanow' ),
					'a' => __( "La carta personalizzata è il cuore del Bundle Deluxe.\n\nDopo l'acquisto ti contattiamo per raccogliere la tua idea: una specie marina a cui sei legato, un ricordo, una dedica.\n\nI nostri artisti la illustrano a mano, prodotta su richiesta appositamente per te, nello stile del resto del mazzo.\n\nÈ un pezzo unico: per questo i tempi di realizzazione sono un po' più lunghi rispetto agli altri set. Ti aggiorniamo a ogni passaggio via email.", 'pharmanow' ),
				),
				array(
					'q' => __( 'Chi ha creato The SIP?', 'pharmanow' ),
					'a' => __( "The SIP — The Sea In your Pocket nasce a Napoli dall'incontro tra arte e scienza.\n\nDietro il progetto ci sono 25 artisti che hanno illustrato le carte e due biologi marini, Manuel e Giuliano, che ne hanno curato i contenuti scientifici.\n\nÈ un progetto partito da una campagna Kickstarter e cresciuto grazie a chi, come te, ama il mare e vuole conoscerlo meglio.", 'pharmanow' ),
				),
			),
		),
		array(
			'id'        => 'ordini',
			'title'     => __( 'Ordini', 'pharmanow' ),
			'icon'      => 'shopping-cart',
			'questions' => array(
				array(
					'q' => __( 'Come faccio un ordine su The SIP?', 'pharmanow' ),
					'a' => __( "Fare un ordine è semplicissimo:\n\n• Scegli il set che preferisci dal catalogo e clicca \"Aggiungi al Carrello\".\n\n• Se vuoi le carte in inglese, ricordati di scriverlo (IT o EN) nel campo note durante il checkout.\n\n• Vai alla cassa, inserisci i tuoi dati e l'indirizzo di spedizione, scegli il metodo di pagamento e conferma.\n\nSubito dopo riceverai una email di conferma con il riepilogo del tuo ordine.", 'pharmanow' ),
				),
				array(
					'q' => __( 'Come vedo il riepilogo del mio ordine?', 'pharmanow' ),
					'a' => __( "Appena completato l'ordine ricevi una email di conferma con tutti i dettagli.\n\nSe hai creato un account, trovi lo storico completo dei tuoi acquisti nella sezione \"Il mio profilo\", alla voce \"I miei ordini\".", 'pharmanow' ),
				),
				array(
					'q' => __( 'Posso modificare o annullare un ordine?', 'pharmanow' ),
					'a' => __( "Puoi annullare o modificare un ordine finché non è ancora stato spedito.\n\nScrivici il prima possibile a info@thesip.it indicando il numero dell'ordine e cosa desideri cambiare: faremo il possibile per aiutarti prima della spedizione.", 'pharmanow' ),
				),
			),
		),
		array(
			'id'        => 'spedizioni',
			'title'     => __( 'Spedizioni e Consegna', 'pharmanow' ),
			'icon'      => 'truck',
			'questions' => array(
				array(
					'q' => __( 'Quanto costa la spedizione?', 'pharmanow' ),
					'a' => __( "La spedizione è GRATUITA per tutti gli ordini superiori a €30.\n\nSotto questa soglia si applica un piccolo contributo per le spese di spedizione, che vedi indicato al checkout prima di pagare.", 'pharmanow' ),
				),
				array(
					'q' => __( 'In quanto tempo ricevo le carte?', 'pharmanow' ),
					'a' => __( "Gli ordini vengono spediti e consegnati in 24/48 ore lavorative.\n\nAppena il pacco parte ricevi una email con il link per seguirne il tracciamento.\n\nFa eccezione il Bundle Deluxe con carta personalizzata: essendo realizzata a mano dagli artisti, i tempi sono più lunghi e te li comunichiamo dopo l'ordine.", 'pharmanow' ),
				),
				array(
					'q' => __( 'Fate la consegna a mano a Napoli?', 'pharmanow' ),
					'a' => __( "Sì! Se sei a Napoli possiamo consegnarti le carte a mano, senza spese di spedizione.\n\nÈ il nostro modo preferito di conoscere chi porta un pezzo di mare a casa. Indicalo nel campo note dell'ordine oppure scrivici a info@thesip.it e ci mettiamo d'accordo su luogo e orario.", 'pharmanow' ),
				),
				array(
					'q' => __( 'Come traccio la mia spedizione?', 'pharmanow' ),
					'a' => __( "A spedizione avvenuta ricevi un'email automatica con il numero di tracking.\n\nPuoi seguire lo stato del pacco cliccando il link nell'email oppure dalla sezione \"I miei ordini\" del tuo profilo.", 'pharmanow' ),
				),
			),
		),
		array(
			'id'        => 'pagamenti',
			'title'     => __( 'Pagamenti', 'pharmanow' ),
			'icon'      => 'credit-card',
			'questions' => array(
				array(
					'q' => __( 'Quali metodi di pagamento accettate?', 'pharmanow' ),
					'a' => __( "Puoi pagare con:\n\n• Carte di credito e debito (Visa, Mastercard, American Express)\n• Carte prepagate\n• PayPal\n\nTutti i pagamenti sono sicuri e criptati: i dati della tua carta non vengono mai conservati sui nostri server.", 'pharmanow' ),
				),
				array(
					'q' => __( 'Il pagamento è sicuro?', 'pharmanow' ),
					'a' => __( "Assolutamente sì. Le transazioni passano attraverso piattaforme certificate come Stripe e PayPal, e tutte le informazioni di pagamento vengono criptate durante la trasmissione.\n\nIn nessun caso The SIP viene a conoscenza del numero della tua carta.", 'pharmanow' ),
				),
				array(
					'q' => __( 'Come uso un codice sconto?', 'pharmanow' ),
					'a' => __( "Durante il checkout trovi un campo dedicato al codice sconto: inseriscilo e clicca \"Applica\" per vedere lo sconto sul totale.\n\nSi può usare un solo codice per ordine e i codici non sono cumulabili con altre promozioni.", 'pharmanow' ),
				),
			),
		),
		array(
			'id'        => 'resi',
			'title'     => __( 'Resi e Rimborsi', 'pharmanow' ),
			'icon'      => 'rotate-ccw',
			'questions' => array(
				array(
					'q' => __( 'Posso restituire il mio ordine?', 'pharmanow' ),
					'a' => __( "Sì, hai diritto di reso entro 14 giorni dalla ricezione, ai sensi del Codice del Consumo.\n\nPer avviarlo scrivici a info@thesip.it indicando il numero dell'ordine. Le carte devono tornarci integre e nella confezione originale.\n\nFanno eccezione le carte personalizzate del Bundle Deluxe: essendo realizzate su misura per te, non sono restituibili, salvo difetto del prodotto.", 'pharmanow' ),
				),
				array(
					'q' => __( 'Come funziona il rimborso?', 'pharmanow' ),
					'a' => __( "Una volta ricevute e verificate le carte, procediamo con il rimborso entro 14 giorni, sullo stesso metodo di pagamento usato per l'acquisto.\n\nRiceverai un'email di conferma appena il rimborso è stato elaborato.", 'pharmanow' ),
				),
				array(
					'q' => __( 'Ho ricevuto un prodotto danneggiato, cosa faccio?', 'pharmanow' ),
					'a' => __( "Ci dispiace! Scrivici a info@thesip.it entro pochi giorni dalla consegna, allegando una foto delle carte e del pacco e indicando il numero dell'ordine.\n\nRisolviamo subito: ti inviamo un nuovo set senza costi aggiuntivi.", 'pharmanow' ),
				),
			),
		),
		array(
			'id'        => 'contatti',
			'title'     => __( 'Contatti', 'pharmanow' ),
			'icon'      => 'message-circle',
			'questions' => array(
				array(
					'q' => __( 'Come posso contattarvi?', 'pharmanow' ),
					'a' => __( "Siamo felici di risponderti: scrivici a info@thesip.it e ti rispondiamo entro 24 ore lavorative.\n\nSe hai domande sulle carte, sulla carta personalizzata o vuoi organizzare una consegna a mano a Napoli, siamo qui.", 'pharmanow' ),
				),
				array(
					'q' => __( 'Come mi iscrivo alla newsletter?', 'pharmanow' ),
					'a' => __( "Trovi il modulo di iscrizione in fondo alla homepage: inserisci la tua email e clicca \"Iscriviti\".\n\nRiceverai in anteprima novità sulle carte, nuove uscite e piccole storie di mare firmate dai nostri artisti e biologi. Nessuno spam: puoi cancellarti quando vuoi.", 'pharmanow' ),
				),
			),
		),
	);

	return apply_filters( 'pn_faq_data', $data );
}
