	var current_transfers = new Array();
	var current_transfers_blocks = new Array();
	var global_pieces_quantity = 0;
	var audio_is_playing = false;
	var global_is_box_barcode = 0;
	var global_view = '';
	var global_current_transfer_destinity = '';
	var global_remove_transfer_id = 0;
//var soporteVibracion= "vibrate" in navigator;
//alert( soporteVibracion );
//window.navigator.vibrate([20, 10, 20]);
//window.navigator.vibrate() ;//&& 
//window.navigator.vibrate(100);


	//var global_focus_locked = 0;
	var element_focus_locked = '';
	//alert( element_focus_locked );

	document.addEventListener('keydown', (event) => {
		var keyValue = event.keyCode;

		if( keyValue == 13 && document.activeElement.id == '' && global_view == '.transfers_products' ){//!= element_focus_locked && element_focus_locked !
			var resp = "<h5 class=\"orange\">No está posicionado en el campo del código de barras!</h5>";
			//alert( '' ) ;
			alert_scann( 'no_focus_audio' );
			
			resp += '<div class="row"><div class="col-2"></div><div class="col-8">';
			resp += '<button class="btn btn-warning form-control" onclick=\"close_emergent();';
			if( element_focus_locked == '' && global_view == '.transfers_products' ){
				//$( '#barcode_seeker' ).focus();
				//$( '#barcode_seeker_lock_btn' ).click();
				resp += "lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', true );";
			}else{//$( element_focus_locked ).focus();
				var aux = element_focus_locked.replace( '#', '' );
				resp += "document.getElementById( '" + aux + "' ).focus();";
			}

			resp += '\"><i class=\"icon-ok-circle\">Aceptar</i></button>';
			resp += '</div></div>';
			$( '.emergent_content' ).html( resp );
			$( '.emergent' ).css( 'display', 'block' );
			//return false;
		}
	}, false);

//mostrar / ocultar vistas del menú
	function show_view( obj, view, make_group = null ){//alert( element_focus_locked );

		if( make_group != null ){
			var url = "ajax/db.php?fl=makeTransfersGroup&transfers=" + current_transfers;
			var response = ajaxR( url );
			if( response != 'ok' ){
				alert( response );
				return false;
			}
		}

		/*if( element_focus_locked != '' && global_view == '.transfers_products'){
			//alert( element_focus_locked );
			return false;
		}*/
		global_view = view;
		if( current_transfers.length <= 0 && ( view == '.transfers_products' || view == '.resume' ) ){
			alert( "Seleccione las transferencia a Validar desde el Listado!" );
			close_emergent();
			return false;
		}
		$('.mnu_item.active').removeClass('active');
		$( obj ).addClass('active');
		$( '.content_item' ).css( 'display', 'none' );
		$( view ).css( 'display', 'block' );
		close_emergent();
		$( '#btn_finish_validation' ).css( 'display', ( view == '.resume' ? 'inline-block' : 'none' ) );

		if( view == '.transfers_products' ){
			lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', true );
			//setTimeout( function (){$( '#barcode_seeker' ).focus(); //alert();}, 300);
		}

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
		/*if( $(obj).attr( 'class' ) == 'btn' ){

		}
		if( )*/
	}
	//redireccionamientos
	function redirect( type ){
		if( global_view == '.transfers_products' && element_focus_locked != '' ){
			return false;
		}
		switch ( type ){
			case 'home' : 
				if( confirm( "Salir sin Guardar?" ) ){
					location.href="../../../../index.php";
				}
			break;
		}
	}
	function close_emergent( obj_to_clean = null, obj_to_focus = null ){
	//cierra emergente
		$( '.emergent_content' ).html( '' );
		$( '.emergent' ).css( 'display', 'none' );
		
		global_pieces_quantity = 0;

		if( global_view == '.transfers_products' ){
			lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', true );
		}
		
		if( obj_to_clean != null ){
			$( obj_to_clean ).val('');
		}
		if( obj_to_focus != null ){
			$( obj_to_focus ).focus();
		}
	}

	function close_emergent_2(){
		$( '.emergent_content_2' ).html( '' );
		$( '.emergent_2' ).css( 'display', 'none' );

	}

	function set_transfers(){
		if( current_transfers.length > 0 ){
			if( !confirm( "Ya hay transferencias validandose, Realmente desea validar nuevas transferencias?" ) ){
				return false;
			}
			current_transfers = new Array();
			current_transfers_blocks = new Array();
		}
		var transfer_to_show = '<h5>Las siguientes transferencias serán verificadas :</h5>';
			var transfer_to_set = '<table class="table table-striped table-bordered">';
		transfer_to_show += '<table class="table table-striped table-bordered">';
			transfer_to_set += '<thead><tr><th>Folio</th><th>Destino</th><th>Quitar</th></tr></thead><tbody id="current_transfers_sets">';
		transfer_to_show += '<thead><tr><th>Folio</th><th>Destino</th><th>Status</th></tr></thead><tbody id="current_transfers_list">';
		$( '#transfers_list_content tr' ).each( function( index ){
			if( $( '#validation_list_8_' + index ).prop( 'checked' ) == true ){
				$( this ).children( 'td' ).each( function (index2){
					if( index2 == 0 ){
						current_transfers.push( parseInt( $( this ).html().trim() ) );
					}else if( index2 == 1 ){
						transfer_to_show += '<tr><td>' + $( this ).html() + '</td>' ;
							transfer_to_set += '<tr><td>' + $( this ).html() + '</td>' ;
					}else if( index2 == 3 ){
						transfer_to_show += '<td>' + $( this ).html() + '</td>';
							transfer_to_set += '<td>' + $( this ).html() + '</td>';
					}else if( index2 == 4 ){
						transfer_to_show += '<td>' + $( this ).html() + '</td></tr>';
							transfer_to_set += '<td><button type="button" class="btn btn-danger btn-trans-del"';
							transfer_to_set += ' onclick="remove_transfer_group( ' + $( '#validation_list_1_' + index ).html().trim() + ' );">';
							transfer_to_set += '<i class="icon-cancel-alt-filled"></i></button></td></tr>';
					}else if( index2 == 5 ){
						var aux = $( '#validation_list_6_' + index ).html().trim();
						if( ! current_transfers_blocks.includes( aux ) ){
							current_transfers_blocks.push( aux );
						}
					}
				});
			}
		});
		if( current_transfers.length <= 0 ){
			alert( "Elije al menos una transferencia para continuar!" );
			current_transfers = new Array();
			current_transfers_blocks = new Array();
			return false;
		}
//console.log( 'Bloques : ',  current_transfers_blocks );
		transfer_to_show += '</tbody></table><br />';
			transfer_to_set += '</tbody></table><br />';
		build_transfers_to_validate( transfer_to_set );
		transfer_to_show += '<div class="row">';
		transfer_to_show += '<div class="col-2"></div>';
		transfer_to_show += '<div class="col-8">';
			transfer_to_show += '<button onclick="show_view( \'.mnu_item.source\', \'.transfers_products\', 1 );" class="btn btn-success form-control">';
				transfer_to_show += 'Aceptar';
			transfer_to_show += '</button>';
			transfer_to_show += '</div>'; 
		transfer_to_show += '</div>'; 

		$( '.emergent_content' ).html( transfer_to_show );
		$( '.emergent' ).css( 'display', 'block' );
		loadLastValidations();
		load_resumen();
	}

	function load_resumen(){
		var response = ajaxR( 'ajax/db.php?fl=getResumeHeader&transfers=' + current_transfers + '&type=1'  );
		$( '.group_card.adjustments.differences' ).html( response );

		response = ajaxR( 'ajax/db.php?fl=getResumeHeader&transfers=' + current_transfers + '&type=2'  );
		$( '.group_card.adjustments.aggregates' ).html( response );
	}
	function build_transfers_to_validate( content ){
		$( '.accordion-body.transfers' ).html( content );
	}
var global_permission_box = 0;
var global_tmp_barcode = '';
var global_tmp_unique_barcode = '';
/*validacion de códigos de barras*/

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
		//global_tmp_barcode = ( global_tmp_barcode == '' && permission_box != null && txt != '' ? txt : global_tmp_barcode );
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
		//alert( ax[0] );
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
				$( '#tmp_sell_barcode' ).focus();
			break; 
			case 'message_info':
				global_tmp_barcode = '';
				global_tmp_unique_barcode = '';
				global_permission_box = '';
				global_pieces_quantity = 0;
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
				loadLastValidations();
				load_resumen();
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
	}

	function setPiecesQuantity(  ){
		global_pieces_quantity = $( '#pieces_quantity_emergent' ).val();
		/*if( barcode == '' || barcode == null ){
			alert( "El código de barras no puede ir vacío" );
		}*/		
		if( global_pieces_quantity <= 0 ){
			alert( "El número de piezas debe ser mayor a Cero!" );
			$( '#pieces_quantity_emergent' ).val( 1 );
			$( '#pieces_quantity_emergent' ).select();
			return false;
		}
		validateBarcode( 'tmp', 'enter', null, global_pieces_quantity );
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

	function loadLastValidations(){
		var url = "ajax/db.php?fl=loadLastValidations&transfers=" + current_transfers;
		var response =  ajaxR( url );
	//alert( response );
		$( '#last_validations' ).html( response );
	}
//confirma envio de excedente
	function confirm_exceeds( permission_box = null ){
	//valida el password del encargado
		var pss = $( '#manager_password' ).val();
		if( pss.length <= 0 ){
			alert( "La contraseña del encargado no puede ir vacía!" );
			$( '#manager_password' ).focus();
			return false;
		}
		var url = 'ajax/db.php?fl=validateManagerPassword&pass=' + pss;
		var response = ajaxR( url );
		if( response != 'ok' ){
			alert( response );
			$( '#response_password' ).html( response );
			$( '#response_password' ).css( 'display', 'block' );
		 	$( '#manager_password' ).select();
			return true;
		}
		//obj, e, permission = null, pieces = null, permission_box = null
		validateBarcode( 'tmp', 'enter', 1, null, ( permission_box != null ? 1 : null ) );
	}//( permission_box != null ? 'tmp' : '#barcode_seeker' )
//regresar el excedente
	function return_exceeds(){
		var return_instructions = '<h5>Aparte este producto de la transferencia para que no sea enviado a la Sucursal!</h5>';
		return_instructions += '<div class="row">';
			return_instructions += '<div class="col-2"></div>';
			return_instructions += '<div class="col-8">';
				return_instructions += '<button class="btn btn-warning form-control" onclick="close_emergent( \'#barcode_seeker\', \'#barcode_seeker\' );">';
					return_instructions += 'Aceptar';
				return_instructions += '</button>';
			'</div>';
		return_instructions += '<div>';
		$( '.emergent_content' ).html( return_instructions );
	}
//agregar proveedor-producto en transferencias
	function save_new_supply( product_id, product_provider, box, pack, piece ){
	//obtiene el valor de la contraseña
		var pss = $( '#manager_password' ).val();
		if( pss.length <= 0 ){
			alert( "La contraseña del encargado es obligatoria!" );
			$( '#manager_password' ).focus();
			return false;
		}
	
		var url = 'ajax/db.php?fl=validateManagerPassword&pass=' + pss;
		var response = ajaxR( url );
		if( response != 'ok' ){
			alert( response );
			$( '#response_password' ).html( response );
			$( '#response_password' ).css( 'display', 'block' );
		 	$( '#manager_password' ).select();
			return true;
		}
	//agrega el registro en la base de datos
		url = "ajax/db.php?fl=insertNewProductValidation&p_id=" + product_id;
		url += "&p_p_id=" + product_provider + "&box=" + box;
		url += "&pack=" + pack + "&piece=" + piece + "&transfers=" + current_transfers;
		//alert( url );
		response = ajaxR( url );
		//alert( response );
	}
//finaliza la validacion de la transferencia
	function finish_validation(){
		if( $( '#validation_resume_1 tr' ).length > 0 ){
			alert( "No se puede terminar la validación de las Transferencias.\nAún hay registros pendientes de validar! " );
			$( '.group_card.adjustments.differences' ).css( 'border', '1px solid red' );
			$( '.group_card.adjustments.differences' ).css( 'background-color', 'orange' );
			setTimeout( function(){
				$( '.group_card.adjustments.differences' ).css( 'border', 'none' );	
				$( '.group_card.adjustments.differences' ).css( 'background-color', 'white' );
			}, 3000 );
			return false;
		}
		var response = ajaxR( 'ajax/db.php?fl=saveValidation&transfers=' + current_transfers );
		var ax = response.split( '|' );
		if( ax[0] == 'ok' ){
			alert( ax[1] );
			location.reload();
		}else{
			alert( "Error : \n" + response );
		}
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
//guardar ajuste de inventario
	function save_adjustment(){
		var data_request_substraction = '', data_request_addition = '', 
			data_request_ok = '';
		var validation_failed = false;
		$( '#inventoryAdjudments tr' ).each( function( index ){
			if( $( '#adjustment_7_' + index ).val().trim() < 0
			|| $( '#adjustment_7_' + index ).val().trim() == '' ){
				validation_failed = '#adjustment_7_' + index;
				return false;
			}
			if( parseInt( $( '#adjustment_8_' + index ).html().trim() ) < 0 ){
				data_request_substraction += ( data_request_substraction != '' ? '|' : '' );
				data_request_substraction += $( '#adjustment_1_' + index ).html().trim();//id de registro
				data_request_substraction += '~' + $( '#adjustment_2_' + index ).html().trim();//id de producto
				data_request_substraction += '~' + $( '#adjustment_3_' + index ).html().trim();//id de proveedor producto
				data_request_substraction += '~' + ( parseInt( $( '#adjustment_8_' + index ).html().trim() ) * -1 );//cantidad para ajustar
			}else if( parseInt( $( '#adjustment_8_' + index ).html().trim() ) > 0 ){
				data_request_addition += ( data_request_addition != '' ? '|' : '' );
				data_request_addition += $( '#adjustment_1_' + index ).html().trim();//id de registro
				data_request_addition += '~' + $( '#adjustment_2_' + index ).html().trim();//id de producto
				data_request_addition += '~' + $( '#adjustment_3_' + index ).html().trim();//id de proveedor producto
				data_request_addition += '~' + parseInt( $( '#adjustment_8_' + index ).html().trim() );//cantidad para ajustar
			}else{
				data_request_ok += ( data_request_ok != '' ? '|' : '' );
				data_request_ok += $( '#adjustment_1_' + index ).html().trim();//id de registro
			}
		});
		if( validation_failed != false ){
			alert( "Aún hay inventarios sin ajustar\nVerifique y vuelva a intentar!" );
			$( validation_failed ).focus();
			return false;
		}
		/*alert( data_request_rest );
		alert( data_request_sum );
		alert( data_request_ok );*/
		var url = 'ajax/db.php?fl=inventoryAdjustment';
		url += '&addition=' + data_request_addition;
		url += '&substraction=' + data_request_substraction;
		url += '&data_ok=' + data_request_ok;
		var response = ajaxR( url );
		$( '.emergent_content' ).html( response ); 
		$( '.emergent' ).css( 'display', 'block' ); 
	}

	function sow_adjustemt_locations( counter ){
		var resp = '<table class="table table-striped table-bordered">';
			resp += '<thead>';
				resp += '<tr>';
					resp += '<th width="20%">#</th>';
					resp += '<th width="80%">Ubicación</th>';
				resp += '</tr>';
			resp += '</thead>';
			resp += '<tbody>';
		var array = $( '#adjustment_9_' + counter ).html().trim().split('~');
			for (var i = 0; i < array.length; i++) {
				resp += '<tr>';
					resp += '<td>' + ( array[i] != 'No hay ubicaciones registradas' ? ( i + 1 ) : '' ) + '</td>';
					resp += '<td>' + array[i] + '</td>';
				resp += '</tr>';
			}
			resp += '</tbody>';
		resp += '</table>';

		resp += '<p align="center">';
			resp += '<button class="btn btn-success" onclick="close_emergent();">';
				resp += '<i class="icon-ok-cirlce">Aceptar</i>';
			resp += '</button>';
		resp += '</p>';

		$( '.emergent_content' ).html( resp );
		$( '.emergent' ).css( 'display', 'block' );
		
	}
	
	function build_adjustemnts_locations(){

	}

	function calculate_adjustment_differece( counter ){
		var virtual_inventory = parseInt( $( '#adjustment_6_' + counter ).html().trim() );
		var physical_inventory = parseInt( $( '#adjustment_7_' + counter ).val().trim() );
		if ( physical_inventory < 0 ){
			alert( "El inventario físico no puede ser menor a cero!" );
			$( '#adjustment_7_' + counter ).val( 0 );
			$( '#adjustment_7_' + counter ).select();
			return false;
		}
		var differece = parseInt( physical_inventory - virtual_inventory );

		$( '#adjustment_8_' + counter ).html( differece );
	}	

//búsqueda de productos recibidos
	function seek_recived_products(){
		var txt = $( '#recived_products_seeker' ).val();
		if( txt.length <= 2 ){
			$( '#recived_products_seeker_response' ).html( '' );
			$( '#recived_products_seeker_response' ).css( 'display', 'none' );
		}
		var url = 'ajax/db.php?fl=seekRecivedProducts&txt=' + txt + '&transfers=' + current_transfers;
		var response = ajaxR( url );
		$( '#recived_products_seeker_response' ).html( response );
		$( '#recived_products_seeker_response' ).css( 'display', 'block' );
	}

	function load_product_validation_detail( obj, product_id ){
		var url = "ajax/db.php?fl=loadProductValidationDetail&product_id=" + product_id + "&transfers=" + current_transfers;
		response = ajaxR( url );
		$( '#last_validations' ).html( response );
		var aux = $( obj ).html().trim().replace( '<b>', '' );
		aux = aux.replace( '</b>', '' );
		$( '#recived_products_seeker' ).val( aux );
		$( '#recived_products_seeker' ).attr( 'disabled', true );
		$( '#recived_products_seeker_response' ).html( '' );
		$( '#recived_products_seeker_response' ).css( 'display', 'none' );
	}

	function clean_recived_form(){
		$( '#recived_products_seeker' ).removeAttr( 'disabled' );
		$( '#recived_products_seeker' ).val( '' );
		$( '#recived_products_seeker_response' ).html( '' );
		$( '#recived_products_seeker_response' ).css( 'display', 'none' );
		loadLastValidations();
	}
/**Fyunciones de bloque de transferencias**/
	function getAllGroup( counter ){
		var val = 1;
		if( global_current_transfer_destinity == '' ){
			global_current_transfer_destinity = $( '#validation_list_4_' + counter ).html().trim();
		}else{
			if( global_current_transfer_destinity != $( '#validation_list_4_' + counter ).html().trim() ){
				alert( "Las transferencias por validar deben de ser de la misma sucursal " );
				if( $( '#validation_list_8_' + counter ).prop( 'checked' ) ){
					 $( '#validation_list_8_' + counter ).removeAttr( 'checked' );
				}
			}
		}
		var block = $( '#validation_list_6_' + counter ).html().trim();
		if( ! $( '#validation_list_8_' + counter ).prop( 'checked' ) ){
			val = 0;
		}
		$( '#transfers_list_content tr' ).each( function( index ){
			if( $( '#validation_list_6_' + index ).html().trim() == block && block != '' ){
				if( val == 1 ){
					$( '#validation_list_8_' + index ).prop( 'checked', true );
				}else{
					$( '#validation_list_8_' + index ).removeAttr( 'checked', true );
				}
			}
		});
	//verifica si ningún check esta checado
		var without_sucursal = 0;
		$( '#transfers_list_content tr' ).each( function( index ){
			if( $( '#validation_list_8_' + index ).prop( 'checked' ) ){
				without_sucursal ++;
			}
		});
		if( without_sucursal == 0 ){
			global_current_transfer_destinity = '';
		}
	}

	function remove_transfer_group( transfer_id ){
		global_remove_transfer_id = transfer_id;
		var remove_all_validation = 0;
		if( $( '#current_transfers_sets tr' ).length <= 1 ){
			remove_all_validation = 1;	
		}
		var url = "ajax/db.php?fl=getPreviousRemoveTransferToValidation&transfer_id=" + transfer_id + "&reset_unic_transfer";
		if( remove_all_validation == 1 ){
			url += "&reset_unic_transfer=1";
		}
		var response = ajaxR( url );
		lock_and_unlock_focus( '#barcode_seeker_lock_btn', '#barcode_seeker', 'lock' );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
		
		if( remove_all_validation == 1 )
			$( '#manager_password' ).focus();
	}

	function confirm_remove_transfer_block(){
		var pss = $( '#manager_password' ).val();
		if( pss.length <= 0 ){
			alert( "La contraseña del encargado no puede ir vacía!" );
			$( '#manager_password' ).focus();
			return false;
		}
		var url = 'ajax/db.php?fl=validateManagerPassword&pass=' + pss;
		var response = ajaxR( url );
		if( response != 'ok' ){
			alert( response );
			/*$( '#response_password' ).html( response );
			$( '#response_password' ).css( 'display', 'block' );*/
		 	$( '#manager_password' ).select();
			return true;
		}
		url = "ajax/db.php?fl=removeTransferBlock&transfer_id=" + global_remove_transfer_id ;
		response = ajaxR( url );
		$( '.emergent_content' ).html( response );
		$( '.emergent' ).css( 'display', 'block' );
	}

	function removeTransferBlockDetail( transfer_product_id ){
		var url = "ajax/db.php?fl=removeTransferBlockDetail&transfer_id=" + global_remove_transfer_id;
			url += "&transfer_product_id=" + transfer_product_id;
		response = ajaxR( url );
		/*if( response!= 'ok' ){
			alert( response );
			return false;
		}else{*/
			$( '#detail_' + transfer_product_id ).remove();
			$( '.emergent_content_2' ).html( response );
			$( '.emergent_2' ).css( 'display', 'block' );
		//}
	}

//var getFormAssignTransfer = ajaxR( "php/formAssignTransfer.php?p_k=" + transfer_id );
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

