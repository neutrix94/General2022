//declaracion de variables
	var current_transfers = new Array();
	var audio_is_playing = false;
	var global_pieces_quantity = 0;


	var element_focus_locked = '';
//mostrar / ocultar vistas del menú
	function show_view( obj, view ){
		if( current_transfers.length == 0 && view == '.receive_transfers' ){
			alert( "Seleccione la(s) transferencia(s) a Recibir desde el Listado!" );
			return false;
		}
		$('.mnu_item.active').removeClass('active');
		$( obj ).addClass('active');
		$( '.content_item' ).css( 'display', 'none' );
		$( view ).css( 'display', 'block' );

		if( view == '.receive_transfers' ){
			lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', true );
			//setTimeout( function (){$( '#barcode_seeker' ).focus(); //alert();}, 300);
		}
	}
//redireccionamientos
	function redirect( type ){
		switch ( type ){
			case 'home' : 
				if( confirm( "Salir sin Guardar?" ) ){
					location.href="../../../../index.php?";
				}
			break;

		}
	}

	function close_emergent( obj_clean = null, obj_focus = null ){
		$( '.emergent_content' ).html( '' );
		$( '.emergent' ).css( 'display', 'none' );
		if( obj_clean != null ){
			$( obj_clean ).val( '' );
		}
		if( obj_focus != null ){
			$( obj_focus ).focus();
		}
	}
//lanza emergente para confirmar transferencias por recibir
	function setTransferToReceive(){
		transfers_to_receive_info = '<div class="transfer_to_receive_container"><div class="row header_transfer_to_receive">'
			+ '<div class="col-6 text-center">Folio</div>'
			+ '<div class="col-6 text-center">Fecha</div>'
		+ '</div>';

		$(".transfers_list_content tr").each(function ( index ) {
			if( $( '#receive_' + index ).prop( 'checked' ) ){
				transfers_to_receive_info += '<div class="row">';
				$(this).children("td").each(function ( index2 ) {
					if( index2 == 0 ){
						current_transfers.push( $( this ).html() );
						transfers_to_receive_info += '<div class="no_visible">' + $( this ).html() + '</div>';
					}else if( index2 <= 2 ){
						transfers_to_receive_info += '<div class="col-6">' + $( this ).html() + '</div>';
					}	
				});
				transfers_to_receive_info += '</div>';
				transfers_to_receive_info += '</div>';
			}
		});
		
		$( '.emergent_content' ).html( 
			'<br /><br />'
			+ '<div style="min-height: 350px;"><p align="center">Las siguentes transferencias serán recibidas :<p>' 
				+ transfers_to_receive_info
				+ '<br />'
				+ '<div class="row">'
					+ '<div class="col-2"></div>'
					+ '<div class="col-8">'
						+ '<button onclick="show_view( \'.mnu_item.source\', \'.receive_transfers\' );close_emergent();" class="btn btn-success form-control">'
							+ 'Confirmar y continuar'
						+ '</button>'
					+'</div>'
					+ '<div class="col-2"></div>'
				+ '</div>'
			+ '</div>' );

		$( '.emergent' ).css( 'display', 'block' );	
		loadLastReceptions();
		receptionResumen( 1 );
		receptionResumen( 2 );
		receptionResumen( 3 );
	}


	function alert_scann( type ){
		if( audio_is_playing ){
			audio = null;
		}
		var audio = document.getElementById(type);
		
		audio_is_playing = true;
		audio.currentTime = 0;
		audio.playbackRate = 1;
		audio.play();

	}

var global_permission_box = 0;
var global_tmp_barcode = '';
var global_tmp_unique_barcode = '';
//validación de códigos de barras
	function validateBarcode( obj, e, permission = null, pieces = null, permission_box = null ){
		var key = e.keyCode;
		var txt = '', unique_code = '';
		if( key != 13 && e != 'enter' ){
			$( '#scanner_products_response' ).css( 'display', 'none' );
			return false;
			
		}
		alert_scann( 'audio' );

		if( obj == 'tmp' ){
			txt = global_tmp_barcode;
		}else{
			if( $( obj ).val().length <= 0 && global_tmp_barcode == '' ){
				alert( "El código de barras no puede ir vacío!" );
				$( obj ).focus();
				return false;
			}
			txt = $( obj ).val().trim();
		}

		
	//omite codigo de barras si es el caso
		var tmp_txt = txt.split( ' ' );
		if( tmp_txt.length == 4 ){
			if( $( '#skip_unique_barcodes' ).val().trim() == 0 ){
				global_tmp_unique_barcode = txt;
			}
			txt = '';
			for ( var i = 0; i < (tmp_txt.length - 1 ); i++ ) {
				txt += ( txt != '' ? ' ' : '' );
				txt += tmp_txt[i];
			}
		}
//alert( txt ); return false;
		global_tmp_barcode = ( global_tmp_barcode == '' && permission_box != null && txt != '' ? txt : global_tmp_barcode );
		var url = "ajax/db.php?fl=validateBarcode";
		url += "&transfers=" + current_transfers;

		url += "&barcode=" + txt/*( global_permission_box != 0 ? global_tmp_barcode : txt )*/;
		
		if( global_pieces_quantity != 0){
			url += "&pieces_quantity=" + global_pieces_quantity;
		}else if( pieces != null ){
			url += "&pieces_quantity=" + pieces;
		}
		if( permission != null ){
			url += "&manager_permission=1";
		}

		if( permission_box != null || global_permission_box == 1 ){
			//global_permission_box = 1;
			url += "&permission_box=" + permission_box;
			//global_permission_box = 0;//oscar
		}else{
			//global_permission_box = 0;
		}

		if( global_tmp_unique_barcode != '' ){
			url += "&unique_code=" + global_tmp_unique_barcode;
		}
//alert( url ); //return false;
		var response =  ajaxR( url );
		//alert( response );
		var ax = response.split( '|' );
		if( ax[0] != 'seeker' ){
			$( '.emergent_content' ).html( ax[1] );
			$( '.emergent' ).css( 'display', 'block' );
		}
		switch( ax[0] ){
			case 'exception_repeat_unic':
				lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				global_tmp_barcode = '';
				global_tmp_unique_barcode = '';
				$( '.barcode_is_repeat_btn' ).focus();
			break; 
			case 'is_box_code':
				lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				setTimeout( function(){ $( '#tmp_sell_barcode' ).focus(); }, 300 );
			break; 
			case 'message_info':
				global_tmp_barcode = '';
				global_tmp_unique_barcode = '';
				global_permission_box = '';
				global_pieces_quantity = 0;
				lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				$( '.emergent_content' ).focus();
			break; 
			case 'manager_password':
				global_tmp_barcode = txt;
				lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				$( '#manager_password' ).focus();
			break; 
			case 'pieces_form':
			alert_scann( 'pieces_number_audio' );
				global_tmp_barcode = txt;
				lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				$( '#pieces_quantity_emergent' ).focus();
			break; 
			case 'is_not_a_box_code':
				lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				$( '#tmp_sell_barcode' ).focus();
			break; 
			case 'amount_exceeded':
				global_tmp_barcode = txt;
				//alert( txt + ' - '  + global_tmp_barcode );
				lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
				$( '#manager_password' ).focus();
			break; 

			case 'seeker':
				$( '#seeker_response' ).html( ax[1] );
				$( '#seeker_response' ).css( 'display', 'block' );
				return false;
			break;

			case 'ok':
				receptionResumen( 1 );
				receptionResumen( 2 );
				receptionResumen( 3 );

				loadLastReceptions();

				$( obj ).val( '' );
				global_pieces_quantity = 0;
				global_tmp_unique_barcode = '';
				global_tmp_barcode = '';
				$( '.emergent_content' ).html( '' );
				$( '.emergent' ).css( 'display', 'none' );

				$( '#scanner_products_response' ).html( ax[1] );
				$( '#scanner_products_response' ).css( 'display', 'block' );

				lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', true );

				$( '#barcode_seeker' ).val( '' );
				$( '#barcode_seeker' ).focus();
				
			break; 
		}
		$( '#seeker_response' ).css( 'display' , 'none' );//oculta resultado de búsqueda
//		$( obj ).val( '' );
		/*global_permission_box = 0;
		global_tmp_barcode = '';*/
		


		/*if( ax[0] == 'seeker' ){
			$( '#seeker_response' ).html( ax[1] );
			$( '#seeker_response' ).css( 'display' , 'block' );
			return false;
		}
		$( '#seeker_response' ).css( 'display' , 'block' );//oculta resultado de búsqueda

		if( ax[0] == 'emergent' && ax[1] != 'is_box_code'){
			//alert( 'is_not_box_code' + ax[1] );
		//muestra contenido
			$( '.emergent_content' ).html( ax[1] );
			$( '.emergent' ).css( 'display', 'block' );
			return false;
		}
		if( ax[1] == 'is_box_code' ){//&& permission_box == null
			//alert( 'is_box_code' );
		//código de barras de caja
			//global_permission_box = 0;
			$( '.emergent_content' ).html( ax[2] );
			$( '.emergent' ).css( 'display', 'block' );
			$( '#tmp_sell_barcode' ).focus();
			//$( '#barcode_seeker' ).val( '' );
			return false;
		}
		
		//alert( 'pasa' + response );
		$( '.emergent_content' ).html( ax[1] );
		$( '.emergent' ).css( 'display', 'block' );	

		if( ax[0] == 'exception_repeat_unic' ){
			global_tmp_barcode = '';
			global_tmp_unique_barcode = '';

		}
		
		if( ax[0] == 'ok' ){

			receptionResumen( 1 );
			receptionResumen( 2 );
			receptionResumen( 3 );

			loadLastReceptions();

			$( obj ).val( '' );
			global_pieces_quantity = 0;
			global_tmp_unique_barcode = '';
			//global_permission_box = 0;
			global_tmp_barcode = '';


			$( '.emergent_content' ).html( '' );
			$( '.emergent' ).css( 'display', 'none' );

			$( '#scanner_products_response' ).html( ax[1] );
			$( '#scanner_products_response' ).css( 'display', 'block' );

			$( '#barcode_seeker' ).val( '' );
			$( '#barcode_seeker' ).focus();
		}*/

		/*var txt = $( obj ).val().trim();
		var url = "ajax/db.php?fl=validateBarcode";
		url += "&transfers=" + current_transfers;
		url += "&barcode=" + txt;
		if( pieces != null ){
			url += "&pieces_quantity=" + pieces;
		}
		if( permission != null ){
			url += "&manager_permission=1";
		}
		//alert( url ); return false;
 		var response =  ajaxR( url );
		//alert( response );
		var ax = response.split( '|' );
		if( ax[0] == 'emergent' ){
		//formulario de piezas
			$( '.emergent_content' ).html( ax[1] );
			$( '.emergent' ).css( 'display', 'block' );
			return false;
		}
		//if( ax[0] == 'ok' ){//recarga los úlmtimos productos recibidos
			$( '.emergent_content' ).html( ax[1] );
			$( '.emergent' ).css( 'display', 'block' );	
			loadLastReceptions();
			receptionResumen( 1 );
			receptionResumen( 2 );
			receptionResumen( 3 );*/
	}

	function lock_and_unlock_focus( obj_btn, obj_field, block = false ){
		if ( ( $( obj_btn ).attr( 'class' ) == 'btn btn-success' && block == false ) || block == 'lock'){
			/*$( obj_btn ).attr( 'class' , 'btn btn-success' );
			$( obj_btn ).html( '<i class="icon-lock-open"></i>' );
			$( obj_field ).removeAttr( 'onblur' );
			element_focus_locked = '';*/
			$( obj_btn ).attr( 'class' , 'btn btn-danger' );
			$( obj_btn ).html( '<i class="icon-lock-open"></i>' );
			$( obj_field ).removeAttr( 'onblur' );
			$( '#barcode_seeker' ).attr( 'disabled', true );
			//$( '#barcode_seeker' ).css( 'background-color', 'red' );
			$( '#barcode_seeker' ).addClass( 'btn btn-danger' );
			$( '#barcode_seeker' ).attr( 'placeholder', 'Presionar botón de candado para habilitar' );
			$( '#barcode_seeker' ).val( '' );
			element_focus_locked = obj_field;
			element_focus_locked = '';
		}else{
			//alert();
			$( obj_btn ).attr( 'class' , 'btn btn-success' );
			$( obj_btn ).html( '<i class="icon-lock"></i>' );
			$( obj_field ).attr( 'onblur', "this.focus();return false;" );
			$( '#barcode_seeker' ).removeAttr( 'disabled' );
			$( '#barcode_seeker' ).removeClass( 'btn btn-danger' );
			$( '#barcode_seeker' ).attr( 'placeholder', 'Escanear / Buscar productos' );
			setTimeout( function(){ $( obj_field ).click();$( obj_field ).focus();}, 300);
		}
	}

	function alert_scann(){
		if( audio_is_playing ){
			audio = null;
		}
		var audio = document.getElementById("audio");
		
		audio_is_playing = true;
		audio.currentTime = 0;
		audio.playbackRate = 1;
		audio.play();
	}

	function setPiecesQuantity(  ){
		var pieces = $( '#pieces_quantity_emergent' ).val();
		/*if( barcode == '' || barcode == null ){
			alert( "El código de barras no puede ir vacío" );
		}*/		
		if( pieces <= 0 ){
			alert( "El número de piezas debe ser mayor a Cero!" );
			$( '#pieces_quantity_emergent' ).val( 1 );
			$( '#pieces_quantity_emergent' ).select();
			return false;
		}
		validateBarcode( '#barcode_seeker', 'enter', null, global_pieces_quantity );
	}

	function setProductByName( product_id ){
		$( '#seeker_response' ).html( '' );
		$( '#seeker_response' ).css( 'display' , 'none' );//oculta resultado de búsqueda
		var url = "ajax/db.php?fl=getOptionsByProductId&product_id=" + product_id;
		var response = ajaxR( url );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function setProductModel(){
		var model_selected = 0;
		$( '#model_by_name_list tr' ).each( function ( index ){
			if( $( '#p_m_5_' + index ).prop( 'checked' ) ){
			//	alert( index );
				model_selected = $( '#p_m_5_' + index ).val();
			}
		});
		if( model_selected == 0 ){
			alert( "Debe de seleccionar un modelo para continuar!" );
			return false;
		}else{
			$( '.emergent_content' ).html( '' );
			$( '.emergent' ).css( 'display', 'none' );
			$( '#barcode_seeker' ).val( model_selected.trim() );
			validateBarcode( '#barcode_seeker', 'enter' );
		}
	}

	function save_new_reception_detail( product_id, product_provider_id, box, pack, piece ){
		var url = "ajax/db.php?fl=insertNewProductReception";
		url += "&transfers=" + current_transfers;
		url += "&p_id=" + product_id + "&p_p_id=" + product_provider_id;
		url += "&box=" + box + "&pack=" + pack + "&piece=" + piece;
		var response = ajaxR( url );
		alert( url );
	}
//
	function loadLastReceptions(){
		var url = "ajax/db.php?fl=loadLastReceptions&transfers=" + current_transfers;
		var response = ajaxR( url );
		$( '#last_received_products' ).html( response );
	}

	function getReceptionProductDetail( product_id, product_provider_id ){
		var url = 'ajax/db.php?fl=getReceptionProductDetail';
		url += '&p_id=' + product_id + "&p_p_id=" + product_provider_id;
		url += '&transfers=' + current_transfers;
		var response = ajaxR( url );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function receptionResumen( type ){		
		var response_obj = "", counter_obj = "";
		var url = "ajax/db.php?fl=getReceptionResumen&transfers=" + current_transfers;
		url += "&type=" + type;
		var response = ajaxR( url );
		switch ( type ){
			case 1 : 
				response_obj = '#transfer_difference';
				counter_obj = '#transfer_difference_counter';
			break;
			case 2 :
				response_obj = '#transfer_excedent';
				counter_obj = '#transfer_excedent_counter';
			break;
			case 3 : 
				response_obj = '#transfer_return';
				counter_obj = '#transfer_return_counter';
			break;
		}
		var aux = response.split( '|' );
		$( counter_obj ).html( aux[0] );
		$( response_obj ).html( aux[1] );
		//$( '#last_received_products' ).html( response );
	}
//resumen del detalle ( resolucion )
	function show_resumen_detail( transfer_id, transfer_product_id, product_id, type ){
		var url = 'ajax/db.php?fl=getProductResolution&t_id=' + transfer_id;
		url += '&t_p=' + transfer_product_id + '&p_id=' + product_id + '&type=' + type;
		var response = ajaxR( url );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
	}
//inserta registros de resolución de transferencias
	function save_resolution ( type ){
		switch( type ){
			case 'missing' : 

			break;

		}
		var url = 'ajax/db.php?fl=saveResolutionRow';
		url += '&product_id=' + $( '#resolution_5_0' ).val();
		url += '&transfer_product_id=' + $( '#resolution_6_0' ).val();
		url += '&quantity=' + $( '#resolution_4_0' ).val();
		url += '&type=' + type;
//alert( url );
		var response = ajaxR( url );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
	}
	
	//llamadas asincronas
	function ajaxR(url){
		if(window.ActiveXObject)
		{		
			var httpObj = new ActiveXObject("Microsoft.XMLHTTP");
		}
		else if (window.XMLHttpRequest)
		{		
			var httpObj = new XMLHttpRequest();	
		}
		httpObj.open("POST", url , false, "", "");
		httpObj.send(null);
		return httpObj.responseText;
	}

