<?php
	include( '../../../../../config.ini.php' );
	include( '../../../../../conect.php' );
	include( '../../../../../conexionMysqli.php' );
	$action = $_GET['fl'];

	switch ( $action ) {
		case 'validateBarcode':
			if( !isset( $_GET['manager_permission'] ) ){
				 $_GET['manager_permission'] = null;
			}
			if( !isset( $_GET['pieces_quantity'] ) ){
				 $_GET['pieces_quantity'] = null;
			}
			if( !isset( $_GET['permission_box'] ) ){
				 $_GET['permission_box'] = null;
			}
			if( !isset( $_GET['unique_code'] ) ){
				 $_GET['unique_code'] = null;
			}
			if( $_GET['barcode'] == '' ){
				$resp = "message_info|<h5 class=\"red\">El código de barras no puede ir vacío</h5>";
				$resp .= "<div class=\"row\">";
					$resp .= "<div class=\"col-2\"></div>";
					$resp .= "<div class=\"col-8\">";
						$resp .= "<button class=\"btn btn-info form-control\" 
										onclick=\"close_emergent( '#barcode_seeker', '#barcode_seeker' );\">
										<i class=\"icon-ok-circle\">Aceptar</i>
								</button>";
					$resp .= "</div>";
				$resp .= "</div>";
				return $resp;
			}
			echo validateBarcode( $_GET['barcode'], $_GET['transfers'], $user_id, 
				$_GET['manager_permission'], $_GET['pieces_quantity'], $_GET['permission_box'], $_GET['unique_code'], $link );
		break;
		case 'insertNewProductValidation' : 
			echo insertNewProductValidation( $_GET['transfers'], $_GET['p_id'], $_GET['p_p_id'], $_GET['box'], $_GET['pack'], $_GET['piece'], $link );
		break;
		case 'loadLastValidations' :
		//die( $_GET['transfers'] );
			echo loadLastValidations( $_GET['transfers'], $user_id, $link );
		break;

		case 'getResumeHeader' : 
			echo getResumeHeader( $_GET['transfers'], $_GET['type'], $link );
		break;

		case 'saveValidation' :
			echo saveValidation( $_GET['transfers'], $link );
		break;

		case 'validateManagerPassword' : 
			echo validateManagerPassword( $_GET['pass'], $link );
		break;

		case 'inventoryAdjustment' :
			echo inventoryAdjustment( $_GET['addition'], $_GET['substraction'], 
				$_GET['data_ok'], $user_id, $link );
		break; 

		case 'getOptionsByProductId' :
			echo getOptionsByProductId( $_GET['product_id'], $link );
		break;

		case 'seekRecivedProducts' : 
			echo seekRecivedProducts( $_GET['txt'], $_GET['transfers'], $link );
		break;

		case 'loadProductValidationDetail' :
			echo loadProductValidationDetail( $_GET['product_id'], $_GET['transfers'], $link );
		break; 

		case 'makeTransfersGroup' :
			echo makeTransfersGroup( $_GET['transfers'], $link );
		break;

		case 'getPreviousRemoveTransferToValidation' : 
			if( !isset( $_GET['reset_unic_transfer'] ) ) {
				$_GET['reset_unic_transfer'] = null;
			}
			echo getPreviousRemoveTransferToValidation( $_GET['transfer_id'], $_GET['reset_unic_transfer'], $link );
		break;

		case 'removeTransferBlockDetail' :
			echo removeTransferBlockDetail( $_GET['transfer_id'], $_GET['transfer_product_id'], $link );
		break;

		case 'removeTransferBlock' :
			echo removeTransferBlock( $_GET['transfer_id'], $link );
		break;

		default:
		//	die( "Permission Denied!" );
		break;
	}

	function validateBarcode( $barcode, $transfers, $user, $excedent_permission = null, 
		$pieces_quantity = null, $permission_box = null, $unique_code = null, $link ){
	//verifica que el código único no haya sido usado anteriormente
		if( $unique_code != null ){
			$sql = "SELECT
						t.folio,
						CONCAT( u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno ) AS name,
						s1.nombre AS origin_name,
						s2.nombre AS destinity_name,
						tcu.id_status_transferencia_codigo AS unique_barcode_status,
						t.id_transferencia
					FROM ec_transferencia_codigos_unicos tcu
					LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
					ON tcu.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
					LEFT JOIN ec_transferencias t
					ON t.id_transferencia = btvd.id_transferencia
					LEFT JOIN sys_users u
					ON u.id_usuario = tcu.id_usuario_validacion
					LEFT JOIN sys_sucursales s1 
					ON s1.id_sucursal = t.id_sucursal_origen
					LEFT JOIN sys_sucursales s2 
					ON s2.id_sucursal = t.id_sucursal_destino
					WHERE tcu.codigo_unico = '{$unique_code}'";
			$stm = $link->query( $sql ) or die( "error|Error al validar si el código único ya fue registrado : {$link->error}" );
			if( $stm->num_rows > 0 ){
				$row = $stm->fetch_assoc();
				$resp = "exception_repeat_unic|<h5 style=\"color : red; text-align : center;\">Este código único ya fue validado anteriormente : </h5>";
				$resp .= "<p><b>Escaneado por :</b> <b class=\"orange\">{$row['name']}</b></p>";
				$resp .= "<p><b>Pertenece a Transferencia : <b class=\"orange\">{$row['folio']}</b></p>";
				$resp .= "<p><b>Sucursal Origen :</b> <b class=\"orange\">{$row['origin_name']}</b></p>";
				$resp .= "<p><b>Sucursal Destino :</b> <b class=\"orange\">{$row['destinity_name']}</b></p>";
				$resp .= "<div class=\"row\">";
					$resp .= "<div class=\"col-3\"></div>";
					$resp .= "<div class=\"col-6\">";
						$resp .= "<button 
									class=\"btn btn-warning form-control barcode_is_repeat_btn\" 
									onclick=\"close_emergent( '#barcode_seeker', '#barcode_seeker' );\">";
							$resp .= "<i class=\"icon-ok-circle\">Aceptar</i>";
						$resp .= "</button>";
					$resp .= "</div>";
				$resp .= "</div>";
				return $resp;
			}
		}
	//verifica si el codigo de caja es de validacion de la caja
			if( $permission_box == null ){
				$sql = "SELECT 
							id_codigo_validacion
						FROM ec_codigos_validacion_cajas
						WHERE codigo_barras = '{$barcode}'";
				$stm = $link->query( $sql ) or die( "Error al consultar si es código de validación de caja : {$link->error}" );
				if( $stm->num_rows == 1 ){
					$resp = 'is_box_code|';
					$resp .= '<div>';
						$resp .= '<div class="row">';
							$resp .= '<div class="col-2"></div>';
							$resp .= '<div class="col-8">';
								$resp .= '<label for="tmp_sell_barcode">El código de barras del sello es válido, para continuar escaneé el código de barras de la caja : </label>';
								$resp .= '<input type="text" id="tmp_sell_barcode" class="form-control" onkeyup="validateBarcode( this, event, null, null, 1 );"><br>';
								$resp .= '<button type="button" class="btn btn-success form-control"';
								$resp .= ' onclick="validateBarcode( \'#tmp_sell_barcode\', \'enter\', null, null, 1 );">';
									$resp .= '<i class="icon-ok-circle">Aceptar</i>';
								$resp .= '</button><br><br>';
								$resp .= '<button type="button" class="btn btn-danger form-control"';
								$resp .= ' onclick="close_emergent( \'#barcode_seeker\' );">';
									$resp .= '<i class="icon-cancel-cirlce">Cancelar</i>';
								$resp .= '</button>';
							$resp .= '</div>';
						$resp .= '</div>';
					$resp .= '</div>';
					return $resp;
				}
			}
	//verifica si el código de barras existe
		$sql = "SELECT
					pp.id_proveedor_producto AS product_provider_id,
					pp.id_producto AS product_id,
					IF( '$barcode' != pp.codigo_barras_pieza_1 AND '$barcode' != pp.codigo_barras_pieza_2 
					 AND '$barcode' != pp.codigo_barras_pieza_3 AND '$barcode' != pp.codigo_barras_presentacion_cluces_1
					 AND '$barcode' != pp.codigo_barras_presentacion_cluces_2 AND '$barcode' != pp.codigo_barras_caja_1 
					 AND '$barcode' != pp.codigo_barras_caja_2 , 1, 0 ) AS is_name_seeker
				FROM ec_proveedor_producto pp
				LEFT JOIN ec_productos p
				ON p.id_productos = pp.id_producto
				WHERE ( pp.codigo_barras_pieza_1 = '{$barcode}' OR pp.codigo_barras_pieza_2 = '{$barcode}' 
				OR pp.codigo_barras_pieza_3 = '{$barcode}' OR pp.codigo_barras_presentacion_cluces_1 = '{$barcode}'
				OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}' OR pp.codigo_barras_caja_1 = '{$barcode}'
				OR pp.codigo_barras_caja_2 = '{$barcode}')";
		//return "error|{$sql}";
		$stm1 = $link->query( $sql ) or die( "error|Error al consultar si el código de barras existe : " . $link->error );
		if( $stm1->num_rows <= 0 ){
			return seekByName( $barcode, $link );
		}
		

	//verifica que el proveedor producto exista en alguna transferencia
		$sql = "SELECT
					tp.id_transferencia_producto AS transfer_product_id,
					tp.id_producto_or AS product_id,
					pp.id_proveedor_producto AS product_provider_id,
					IF( '$barcode' = pp.codigo_barras_pieza_1 OR '$barcode' = pp.codigo_barras_pieza_2 
					OR '$barcode' = pp.codigo_barras_pieza_3, 1, 0 ) AS piece,
					IF( '$barcode' = pp.codigo_barras_presentacion_cluces_1 OR '$barcode' = pp.codigo_barras_presentacion_cluces_2,
					1, 0 ) AS pack,
					IF( '$barcode' = pp.codigo_barras_caja_1 OR '$barcode' = pp.codigo_barras_caja_2,
					1, 0 ) AS 'box',
					tp.cantidad_cajas,
					tp.cantidad_paquetes,
					tp.cantidad_piezas,
					tp.cantidad,
					SUM( IF( tvu.id_transferencia_validacion IS NULL, 
							0, 
							( tvu.cantidad_cajas_validadas * pp.presentacion_caja ) 
						) 
					) AS boxes_recived,
					SUM(IF( tvu.id_transferencia_validacion IS NULL, 
							0, 
							( tvu.cantidad_paquetes_validados * pp.piezas_presentacion_cluces ) 
						) 
					) AS packs_recived,
					SUM(IF( tvu.id_transferencia_validacion IS NULL, 
							0, 
							tvu.cantidad_piezas_validadas 
						) 
					) AS pieces_recived 
				FROM ec_transferencia_productos tp/*ec_transferencias_surtimiento_usuarios tsu*/
				/*ON tp.id_transferencia_producto = tsu.id_transferencia_producto*/
				LEFT JOIN ec_transferencias t 
				ON tp.id_transferencia = t.id_transferencia
				LEFT JOIN ec_productos p 
				ON tp.id_producto_or = p.id_productos
				LEFT JOIN ec_proveedor_producto pp
				ON tp.id_proveedor_producto = pp.id_proveedor_producto
				LEFT JOIN ec_transferencias_validacion_usuarios tvu 
				ON tp.id_transferencia_producto = tvu.id_transferencia_producto
				WHERE t.id_transferencia IN( {$transfers} )
				AND ( ( pp.codigo_barras_pieza_1 = '{$barcode}' OR pp.codigo_barras_pieza_2 = '{$barcode}' 
					OR pp.codigo_barras_pieza_3 = '{$barcode}' OR pp.codigo_barras_presentacion_cluces_1 = '{$barcode}'
					OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}' OR pp.codigo_barras_caja_1 = '{$barcode}'
					OR pp.codigo_barras_caja_2 = '{$barcode}')
					/*OR p.nombre LIKE '%{$barcode}%'*/
				)
				GROUP BY tp.id_transferencia_producto";
//die('error|' . $sql);
		$stm2 = $link->query( $sql ) or die( "error|Error al buscar el producto por código de barras :  " . $link->error );
	//verifica si el producto existe en la transferencia
		if( $stm2->num_rows <= 0 ){
			$sql = "SELECT
					tp.id_transferencia_producto AS transfer_product_id,
					tp.id_producto_or AS product_id,
					pp.id_proveedor_producto AS product_provider_id,
					IF( '$barcode' = pp.codigo_barras_pieza_1 OR '$barcode' = pp.codigo_barras_pieza_2 
					OR '$barcode' = pp.codigo_barras_pieza_3, 1, 0 ) AS piece,
					IF( '$barcode' = pp.codigo_barras_presentacion_cluces_1 OR '$barcode' = pp.codigo_barras_presentacion_cluces_2,
					1, 0 ) AS pack,
					IF( '$barcode' = pp.codigo_barras_caja_1 OR '$barcode' = pp.codigo_barras_caja_2,
					1, 0 ) AS 'box'
				FROM ec_proveedor_producto pp
				LEFT JOIN ec_transferencia_productos tp
				ON tp.id_producto_or = pp.id_producto
				LEFT JOIN ec_transferencias t ON tp.id_transferencia = t.id_transferencia
				WHERE t.id_transferencia IN( {$transfers} )
				AND ( pp.codigo_barras_pieza_1 = '{$barcode}' OR pp.codigo_barras_pieza_2 = '{$barcode}' 
					OR pp.codigo_barras_pieza_3 = '{$barcode}' OR pp.codigo_barras_presentacion_cluces_1 = '{$barcode}'
					OR pp.codigo_barras_presentacion_cluces_2 = '{$barcode}' OR pp.codigo_barras_caja_1 = '{$barcode}'
					OR pp.codigo_barras_caja_2 = '{$barcode}')";
//return '|' . $sql;
			$stm3 = $link->query( $sql ) or die( "Error al consultar si el producto existe en la transferencia : " . $link->error );
			if( $stm3->num_rows <= 0){
				$resp = 'message_info|<br/><h3 class="inform_error">El producto no pertenece a esta(s) Transferencia(s).<br />Verifique los datos y vuelva a intentar</h3>';
					$resp .= '<div class="row"><div class="col-2"></div><div class="col-8">';
				$resp .= '<button class="btn btn-danger form-control" onclick="close_emergent( \'#barcode_seeker\', \'#barcode_seeker\' );">Aceptar</button></div><br/>';
				return $resp;
			}else{
				//die( '|here' );
				$inform = $stm3->fetch_assoc();
				$resp = 'manager_password|<br/><h3 class="inform_error">El modelo del producto es incorrecto<br />Si se va a enviar, pida la autorización del encargado : </h3>'; 
				$resp .= '<div class="row"><div class="col-2"></div><div class="col-8">';
				$resp .= '<input type="password" id="manager_password" class="form-control emergent_manager_password"><br>';
				$resp .= '<button class="btn btn-success form-control" onclick="save_new_supply( ';
					$resp .= " {$inform['product_id']}, {$inform['product_provider_id']}, {$inform['box']}, {$inform['pack']}, {$inform['piece']} ";
				$resp .= ' );">Aceptar</button> <br><br>';
				
				$resp .= '<button class="btn btn-danger form-control" onclick="close_emergent( \'#barcode_seeker\', \'#barcode_seeker\' );">';
				$resp .= 'Cancelar</button></div></div><br>';
					
				return $resp;
			}
		}
		$row = $stm2->fetch_assoc();
		if( $row['piece'] == 1 && $pieces_quantity == null 
			&& $excedent_permission == null && $permission_box == '' ){
		//regresa formulario de piezas
			$resp = 'pieces_form|<div class="row">';
					$resp .= '<div><h5>Ingrese el número de Piezas : </h5></div>';
					$resp .= '<div class="col-2"></div>';
					$resp .= '<div class="col-8">';
						$resp .= '<input type="number" class="form-control" id="pieces_quantity_emergent">';
						$resp .= '<button type="button" class="btn btn-success form-control"';
						$resp .= ' onclick="setPiecesQuantity();">';
							$resp .= 'Aceptar';
						$resp .= '</button><br><br>';
						$resp .= '<button type="button" class="btn btn-danger form-control"';
						$resp .= ' onclick="close_emergent( \'#barcode_seeker\', \'#barcode_seeker\' );">';
							$resp .= 'Cancelar';
						$resp .= '</button>';
					$resp .= '</div>';
				$resp .= '</div>';
			$resp .= '</div>';
			return $resp;
		}

		if( $permission_box == null && $row['box'] == 1 ){
			$resp = 'message_info|<div class="row">';
				$resp .= '<div class="col-2"></div>';
				$resp .= '<div class="col-8"><h5>Para escanear la caja primero escaneé el sello de caja, si este esta roto escaneé los paquetes </h5>';
					$resp .= '<button type="button" class="btn btn-success form-control"';
					$resp .= ' onclick="close_emergent( \'#barcode_seeker\' );">';
						$resp .= 'Aceptar';
					$resp .= '</button>';
				$resp .= '</div>';
			$resp .= '</div>';
			return $resp;
		}else if( $permission_box != null && $row['box'] != 1 ){
				$resp = 'is_not_a_box_code|';
				$resp .= '<div>';
					$resp .= '<div class="row">';
						$resp .= '<div class="col-2"></div>';
						$resp .= '<div class="col-8">';
							$resp .= '<label for="tmp_sell_barcode">El código de barras no pertenece a una caja, para continuar escaneé el código de barras de la caja : </label>';
							$resp .= '<input type="text" id="tmp_sell_barcode" class="form-control"><br>';
							$resp .= '<button type="button" class="btn btn-success form-control"';
							$resp .= ' onclick="validateBarcode( \'#tmp_sell_barcode\', \'enter\', null, null, 1 );">';
								$resp .= '<i class="icon-ok-circle">Aceptar</i>';
							$resp .= '</button><br>';
							$resp .= '<button type="button" class="btn btn-danger form-control"';
							$resp .= ' onclick="close_emergent( \'#barcode_seeker\' );">';
								$resp .= '<i class="icon-cancel-cirlce">Cancelar</i>';
							$resp .= '</button>';
						$resp .= '</div>';
					$resp .= '</div>';
				$resp .= '</div>';
				return $resp;
		}

		if( $pieces_quantity != null ){
			$row['piece'] = $pieces_quantity;
		}//die( 'example|' . $row['product_id'] );
		return insertProductValidation( $row, $user, $transfers, $excedent_permission, $permission_box, $unique_code, $link );
		
	}

	function insertProductValidation( $data, $user, $transfers, $excedent_permission = null, $permission_box = 1, $unique_code = null, $link ){

		$link->autocommit( false );
	//verifica transferencias pendientes de validación	
		$sql = "SELECT 
					ax.product_transfer_id,
					ax.boxes_to_validate,
					ax.packs_to_validate,
					ax.pieces_to_validate,
					ax.pending_to_validate
				FROM(
					SELECT
						tp.id_transferencia_producto AS product_transfer_id,
						( SUM( IF( tp.cantidad_cajas_surtidas = 0, tp.cantidad_cajas, tp.cantidad_cajas) ) - SUM( tp.cantidad_cajas_validacion ) ) AS boxes_to_validate,
						( SUM( IF( tp.cantidad_paquetes_surtidos = 0, tp.cantidad_paquetes, tp.cantidad_paquetes_surtidos ) ) - SUM( tp.cantidad_paquetes_validacion ) ) AS packs_to_validate,
						( SUM( IF( tp.cantidad_piezas_surtidas = 0, tp.cantidad_piezas, tp.cantidad_piezas_surtidas ) ) - SUM( tp.cantidad_piezas_validacion ) ) AS pieces_to_validate,
						( SUM( IF( tp.total_piezas_surtimiento = 0, tp.cantidad, tp.total_piezas_surtimiento ) ) - SUM( tp.total_piezas_validacion ) ) AS pending_to_validate
					FROM ec_transferencia_productos tp
				/*LEFT JOIN ec_productos p ON tp.id_producto_or = p.id_productos*/
				WHERE tp.id_transferencia IN( {$transfers} )
				AND tp.id_producto_or = '{$data['product_id']}'
				AND tp.id_proveedor_producto = '{$data['product_provider_id']}'
				GROUP BY tp.id_transferencia_producto
				/*AND SUM( tp.total_piezas_surtimiento ) > SUM( tp.total_piezas_validacion )*/
				)ax
				WHERE ax.pending_to_validate > 0
				GROUP BY ax.product_transfer_id";
		$stm = $link->query( $sql ) or die( "error|Error al consultar transferencias pendientes de validar : " . $link->error );
	//verifica que la cantidad que se va a validar no supere la cantidad pedida
		$sql = "SELECT 
					CONCAT( p.nombre, ' <b> ( MODELO : ', pp.clave_proveedor, ' )</b>' ) AS description_name,
					SUM( IF( tp.total_piezas_surtimiento = 0, tp.cantidad, tp.total_piezas_surtimiento ) )
					- SUM( tp.total_piezas_validacion ) AS total_to_validation,
					SUM( IF( tp.total_piezas_surtimiento = 0, tp.cantidad, tp.total_piezas_surtimiento ) ) AS pieces_total,
					SUM( tp.total_piezas_validacion ) AS validated_pieces,
					( ( pp.presentacion_caja * {$data['box']} ) 
								+ ( pp.piezas_presentacion_cluces * {$data['pack']} ) 
								+ {$data['piece']} ) AS supplie
				FROM ec_transferencia_productos tp
				LEFT JOIN ec_proveedor_producto pp
				ON pp.id_proveedor_producto = tp.id_proveedor_producto
				LEFT JOIN ec_productos p
				ON p.id_productos = pp.id_producto
				WHERE tp.id_transferencia IN( {$transfers} )
				AND tp.id_producto_or = '{$data['product_id']}'
				AND tp.id_proveedor_producto = '{$data['product_provider_id']}'";
		$stm2 = $link->query( $sql );
		$comparation_row = $stm2->fetch_assoc();
		//return 'error|'. $sql;
		$description = '';
		if( ( $stm->num_rows <= 0 || $comparation_row['supplie'] > $comparation_row['total_to_validation'] ) 
			&& $excedent_permission == null ){
			//while( $r = $stm->fetch_assoc() ){
				$numeric_value = '';
				if( $data['piece'] != 0 ){
					$numeric_value = $data['piece'];
					$description = 'La pieza';
				}else if( $data['pack'] != 0 ){
					$numeric_value = $data['pack'];
					$description = 'El paquete';
				}else if( $data['box'] != 0 ){
					$numeric_value = $data['box'];
					$description = 'La caja';
				}
			$resp = 'amount_exceeded|<h5>' . $description . ' que escaneo supera la cantidad surtida, si se va a enviar';

			$resp .= ' pida la autorización del encargado : </h5>';
			$resp .= "<p class=\"orange\">{$comparation_row['description_name']}</p>";//{$sql}
//$resp .= $sql;

	//$resp .= '<p>perm : ' . $excedent_permission  . '</p>';
			
			$resp .= '<div class="row"><div class="col-2"></div>';
				$resp .= '<div class="col-8">';
					$resp .= '<div class="row">';
						$resp .= '<div class="col-6">';
							$resp .= "<p align=\"center\">Cantidad total de surtimiento : <br><b class=\"orange\">{$comparation_row['pieces_total']}</b></p>";
						$resp .= '</div>';
						$resp .= '<div class="col-6">';
							$resp .= "<p align=\"center\">Cantidad surtida : <br><b class=\"orange\">{$comparation_row['validated_pieces']}</b></p>";
						$resp .= '</div>';
						$resp .= '<div class="col-6">';
							$resp .= "<p align=\"center\">Cantidad pendiente de validar : <br><b class=\"orange\">" . ($comparation_row['total_to_validation'] <= 0 ? 0 : $comparation_row['total_to_validation']) . "</b></p>";
						$resp .= '</div>';
						$resp .= '<div class="col-6">';
							$resp .= "<p align=\"center\">Cantidad que se intenta validar : <br><b class=\"orange\">{$comparation_row['supplie']}</b></p>";
						$resp .= '</div>';
					$resp .= '</div>';
					
					$resp .= '<input type="password" class="form-control" id="manager_password">';
					$res .= '<p id="response_password"></p>';
					$resp .= '<button type="button" class="btn btn-success form-control';
						$resp .= ' form-control" onclick="confirm_exceeds( ' . ( $permission_box == 1 ? '1'  : '' ) . ' );">';
						$resp .= '<i class="icon-ok-circle">Aceptar</i>';
					$resp .= '</button>';

					$resp .= '<button type="button" class="btn btn-danger form-control';
						$resp .= ' form-control" onclick="return_exceeds();">';
						$resp .= '<i class="icon-ok-circle">Regresar producto</i>';
					$resp .= '</button>';
				$resp .= '</div>';
			$resp .= '</div>';
			return $resp;
		}
	//inserta el registro de validación
		$sql = "INSERT INTO ec_transferencias_validacion_usuarios ( id_transferencia_validacion, id_transferencia_producto,
		id_usuario, id_producto, id_proveedor_producto, cantidad_cajas_validadas, cantidad_paquetes_validados, cantidad_piezas_validadas, fecha_validacion, id_status )
		VALUES( NULL, '{$data['transfer_product_id']}', '{$user}', '{$data['product_id']}', '{$data['product_provider_id']}', 
			'{$data['box']}', '{$data['pack']}', '{$data['piece']}', NOW(), 1 )";
		$stm = $link->query( $sql ) or die( "error|Error al insertar el registro de validación : " . $link->error );
		$validation_detail_id = $link->insert_id;
		//die( '|Error : ' . $sql );
	//actualiza la validacion del producto en la transferencia
		$sql = "UPDATE ec_transferencia_productos tp 
				LEFT JOIN ec_proveedor_producto pp 
				ON tp.id_proveedor_producto = pp.id_proveedor_producto
			SET tp.cantidad_cajas_validacion =  ( tp.cantidad_cajas_validacion + {$data['box']} ),
			tp.cantidad_paquetes_validacion =  ( tp.cantidad_paquetes_validacion + {$data['pack']} ),
			tp.cantidad_piezas_validacion =  ( tp.cantidad_piezas_validacion + {$data['piece']} ),
			tp.total_piezas_validacion = ( ( pp.presentacion_caja * tp.cantidad_cajas_validacion ) 
								+ ( pp.piezas_presentacion_cluces * tp.cantidad_paquetes_validacion ) 
								+ tp.cantidad_piezas_validacion )
			WHERE tp.id_transferencia_producto = '{$data['transfer_product_id']}'
			AND pp.id_proveedor_producto = '{$data['product_provider_id']}'";
		$stm = $link->query( $sql ) or die( "error|Error al actualizar las piezas validadas en la transferencia : " . $link->error );
	//código unico
		if( $unique_code != null ){
			$sql = "INSERT INTO ec_transferencia_codigos_unicos ( /*1*/id_transferencia_codigo, /*2*/id_bloque_transferencia_validacion,
				/*3*/id_bloque_transferencia_recepcion, /*4*/id_usuario_validacion, /*5*/id_usuario_recepcion, /*6*/id_status_transferencia_codigo, 
				/*7*/nombre_status, /*8*/fecha_alta, /*9*/codigo_unico, /*10*/piezas_contenidas, /*11*/id_transferencia_validacion )
				SELECT 
					/*1*/NULL, 
					/*2*/btv.id_bloque_transferencia_validacion,
					/*3*/NULL,
					/*4*/{$user}, 
					/*5*/NULL, 
					/*6*/1, 
					/*7*/(SELECT nombre_status FROM ec_status_transferencias_codigos_unicos WHERE id_status_transferencia_codigo = 1), 
					/*8*/NOW(),
					/*9*/'{$unique_code}',
					/*10*/( SELECT 
								( {$data['box']} * pp.presentacion_caja )
								+ ( {$data['pack']} * pp.piezas_presentacion_cluces )
								+ ( {$data['piece']} )
							FROM ec_proveedor_producto pp
							WHERE pp.id_proveedor_producto = {$data['product_provider_id']}
						),
					/*11*/{$validation_detail_id}
				FROM ec_transferencia_productos tp
				LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
				ON tp.id_transferencia = btvd.id_transferencia
				LEFT JOIN ec_bloques_transferencias_validacion_detalle btv
				ON btvd.id_bloque_transferencia_validacion = btv.id_bloque_transferencia_validacion
				WHERE tp.id_transferencia_producto = {$data['transfer_product_id']}
				GROUP BY tp.id_transferencia_producto";
			$stm = $link->query( $sql ) or die( "error|Error al insertar el código único : {$link->error}" );
		}
	//asigna ajuste de inventario pendiente
		$sql = "UPDATE ec_diferencias_inventario_proveedor_producto 
					SET id_usuario_resuelve = '{$user}'
				WHERE id_transferencia_producto = '{$data['transfer_product_id']}'
				AND ajustado = '0'";
		$stm = $link->query( $sql ) or die( "Error al actualizar el usuario en el ajuste : {$link->error}" );

		$link->autocommit( true );

		$resp = '<div class="row">';
			$resp .= '<div class="col-3"></div>';
			$resp .= '<div class="col-6">';
				$resp .= '<button class="btn btn-success form-control" onclick="close_emergent( \'#barcode_seeker\', \'#barcode_seeker\' );">';
					$resp .= '<i class="icon-ok-circle">Aceptar</i>';
				$resp .= '</button>';
			$resp .= '</div>';
		$resp .= '</div>';
		
		return "ok|<p align=\"center\">Código Validado exitosamente</p>";//{$resp}
	}

	function loadLastValidations( $transfers, $user, $link ){
		$sql = "SELECT
					tvu.id_transferencia_validacion AS transfer_validation_id,
					p.id_productos AS product_id,
					CONCAT( p.nombre, ' ( MODELO : <b>', pp.clave_proveedor, '</b> )' ) AS name,
					t.id_transferencia AS transfer,
					IF(	tvu.cantidad_cajas_validadas > 0, 
						CONCAT( tvu.cantidad_cajas_validadas, ' caja', IF( tvu.cantidad_cajas_validadas > 1, 's', '' )),
						IF( tvu.cantidad_paquetes_validados > 0,
							CONCAT( tvu.cantidad_paquetes_validados, ' paquete', IF( tvu.cantidad_cajas_validadas > 1, 's', '' )),
							CONCAT( tvu.cantidad_piezas_validadas, ' pieza', IF( tvu.cantidad_piezas_validadas > 1, 's', '' ))
						)
					) AS recived
				FROM ec_transferencias_validacion_usuarios tvu
				LEFT JOIN ec_transferencia_productos tp 
				ON tvu.id_transferencia_producto = tp.id_transferencia_producto
				LEFT JOIN ec_transferencias t ON tp.id_transferencia = t.id_transferencia
				LEFT JOIN ec_productos p ON tvu.id_producto = p.id_productos
				LEFT JOIN ec_proveedor_producto pp 
				ON tvu.id_proveedor_producto = pp.id_proveedor_producto
				WHERE t.id_transferencia IN( {$transfers} )
				AND tvu.id_usuario = '{$user}'
				ORDER BY tvu.id_transferencia_validacion DESC
				LIMIT 3";
				//die( $sql );
		$stm = $link->query( $sql )or die( "Error al consultar las últimas revisiones : " . $link->error );
		return buildLastValidations( $stm );	
	}

	function buildLastValidations( $stm ){
		$resp = '';
		while ( $row = $stm->fetch_assoc() ) {
			$resp .= '<tr>';
			$resp .= '<td class="no_visible">' . $row['transfer_validation_id'] . '</td>';
			$resp .= '<td>' . $row['name'] . '</td>';
			$resp .= '<td>' . $row['recived'] . '</td>';
			$resp .= '<td>' . $row['transfer'] . '</td>';
			$resp .= '</tr>';
		}
		return $resp;
	}
/*generacion de tablas de resumen*/
	function getResumeHeader( $transfers, $type, $link ){
		if( $type == 1 ){
			$title = 'Partidas Pendientes';
		}else{
			$title = 'Partidas Agregadas ( autorizadas )';
		}
		$resp = '<center class="list_header_sticky top-10"><h6><b>' . $title . '</b></h6></center>';
		$resp .= '<table class="table table-bordered table-striped table_70">';
			$resp .= '<thead class="list_header_sticky top8">';
				$resp .= '<tr>';
					$resp .= '<th>#</th>';
					$resp .= '<th>Producto</th>';
					$resp .= '<th>Transferencia</th>';
					$resp .= '<th>';
					$resp .= ( $type == 1 ? 'Faltante' : 'Agregadas' );
					$resp .= '</th>';
				$resp .= '</tr>';
			$resp .= '</thead>';
			$resp .= '<tbody id="validation_resume_' . $type . '">';
			$resp .= getResumeRows( $transfers, $type, $link );
			$resp .= '</tbody>';
		$resp .= '</table>';
		return $resp;
	}

/*generacion de registros de resumen*/
	function getResumeRows( $transfers, $type, $link ){
		$resp = '';
		if( $type == 1 ){
			$sql = "SELECT
						ax.name,
						ax.reference,
						ax.difference,
						ax.assortment_quantity
					FROM(
						SELECT
							CONCAT( p.nombre, ' <b>', pp.clave_proveedor, '<b>' ) AS name,
							t.id_transferencia AS reference, 
							SUM( IF(tp.total_piezas_surtimiento = 0, tp.cantidad, tp.total_piezas_surtimiento) ) - SUM( tp.total_piezas_validacion ) AS difference,
								tp.total_piezas_surtimiento AS assortment_quantity,
							tp.id_transferencia_producto As transfer_product_id
						FROM ec_transferencia_productos tp
						LEFT JOIN ec_productos p 
						ON tp.id_producto_or = p.id_productos
						LEFT JOIN ec_proveedor_producto pp 	
						ON tp.id_proveedor_producto = pp.id_proveedor_producto
						LEFT JOIN ec_transferencias t 
						ON tp.id_transferencia = t.id_transferencia
						LEFT JOIN ec_transferencias_resolucion tr
						ON tr.id_transferencia_producto = tp.id_transferencia_producto
						WHERE tp.id_transferencia IN( {$transfers} )
						AND tr.id_transferencia_producto IS NULL
						GROUP BY tp.id_transferencia_producto, tp.id_proveedor_producto
					)ax
					WHERE ax.difference > 0
					GROUP BY ax.transfer_product_id";
               //die( $sql );
        }else{
			$sql = "SELECT
					CONCAT( p.nombre, ' <b>', pp.clave_proveedor, '</b>' ) AS name,
					t.id_transferencia AS reference, 
					SUM( tp.cantidad ) AS difference,
					tp.total_piezas_surtimiento AS assortment_quantity
				FROM ec_transferencia_productos tp
				LEFT JOIN ec_productos p 
				ON tp.id_producto_or = p.id_productos
				LEFT JOIN ec_proveedor_producto pp 
				ON tp.id_proveedor_producto = pp.id_proveedor_producto
				LEFT JOIN ec_transferencias t 
				ON tp.id_transferencia = t.id_transferencia
				WHERE tp.id_transferencia IN( {$transfers} )
				AND tp.agregado_surtimiento_validacion = 1
                GROUP BY tp.id_transferencia_producto, tp.id_proveedor_producto";/*
                ORDER BY CONCAT( p.nombre, pp.clave_proveedor )*/
        }
		$stm = $link->query( $sql ) or die( "Error al consultar registros pendientes de validar : " . $link->error );
		if( $stm->num_rows <= 0 ){
			return '';
		}
		$counter = 0;
		while ( $row = $stm->fetch_assoc() ) {
			$counter ++;
			if( $row['name'] != '' && $row['name'] != null ){
				$resp .= '<tr';
				$resp .= ( $row['assortment_quantity'] == 0 ? ' class="no_assortment_row"' : '' );
				$resp .= '>';
					//$resp .= '<td class="no_visible">' . $row[''] . '</td>';
					$resp .= '<td>' . $counter . '</td>';
					$resp .= '<td>' . $row['name'] . '</td>';
					$resp .= '<td>' . $row['reference'] . '</td>';
					$resp .= '<td align="right">' . $row['difference'] . '</td>';
				$resp .= '</tr>';
			}
		}
		return $resp;
	}

	function saveValidation( $transfers, $link ){
		$link->autocommit( false );
		//manda a hacer las resoluciones correspondientes
		$resolutions = makeResolutionValidation( $transfers, $link );
		if( $resolutions != 'ok' ){
			return $resolutions;
		}
		$sql = "UPDATE ec_transferencias SET id_estado = 7 WHERE id_transferencia IN( {$transfers} )";
		$stm = $link->query( $sql ) or die( "Error al actualizar las Trasnferencias a Validadas : " . $link->error );
		$link->autocommit( true );
		return 'ok|Transferencias Validadas exitosamente!';
	}
//resolución de la validación
	function makeResolutionValidation( $transfers, $link ){
		$resp = 'ok';
		$link->autocommit( false );
	//colnsulta las transferencias con diferencia
		$sql = "SELECT
					t.id_transferencia AS transfer_id,
                    ma.id_movimiento_almacen AS warehouse_movement_id
					/*GROUP_CONCAT( tp.id_transferencia_producto SEPARATOR ',' )*/
				FROM ec_transferencia_productos tp
				LEFT JOIN ec_transferencias t 
				ON t.id_transferencia = tp.id_transferencia
                LEFT JOIN ec_movimiento_almacen ma
                ON ma.id_transferencia = t.id_transferencia
				WHERE tp.cantidad != tp.total_piezas_validacion
				AND t.id_transferencia IN( {$transfers} )
				GROUP BY tp.id_transferencia";
		$stm = $link->query( $sql ) or die( "Error al consultar las transferencias con diferencias : {$link->error}" );

		while( $row = $stm->fetch_assoc() ){
			$sql = "INSERT INTO ec_movimiento_detalle ( /*1*/id_movimiento_almacen_detalle, /*2*/id_movimiento, /*3*/id_producto,
						/*4*/cantidad, /*5*/cantidad_surtida, /*6*/id_pedido_detalle, /*7*/id_oc_detalle, /*8*/id_proveedor_producto )
					SELECT
						/*1*/NULL,
						/*2*/{$row['warehouse_movement_id']},
						/*3*/tp.id_producto_or,
						/*4*/( tp.total_piezas_validacion - tp.cantidad ),
						/*5*/( tp.total_piezas_validacion - tp.cantidad ),
						/*6*/-1,
						/*7*/-1,
						/*8*/tp.id_proveedor_producto
					FROM ec_transferencia_productos tp
					WHERE tp.id_transferencia IN( '{$row['transfer_id']}' )
					AND tp.cantidad <> tp.total_piezas_validacion";
		//	echo $sql;
			$stm2 = $link->query( $sql ) or die( "Error al insertar los movimientos de la resolución : {$link->error}" );
		}
		$link->autocommit( true );
		return $resp;
	}

	function validateManagerPassword( $password, $link ){
		$sql = "SELECT id_usuario FROM sys_users WHERE contrasena = md5( '{$password}' )";
		$stm = $link->query( $sql ) or die( "Error al verificar password de encargado : " . $link->error );
		if( $stm->num_rows <= 0 ){
			die( 'La contraseña del encargado es incorrecta.' );
		}
		return 'ok';
	}


	function insertNewProductValidation( $transfers, $product_id, $product_provider_id, $box, $pack, $piece, $link ){
		//die( 'ok|here' );
	//verifica a ue transferencia se le asignara el producto
		$sql = "SELECT 
					t.id_transferencia AS transfer_id,
					ma.id_movimiento_almacen AS mov_id,
					SUM( ( tp.cantidad - tp.total_piezas_validacion ) ) AS difference
				FROM ec_transferencias t
				LEFT JOIN ec_transferencia_productos tp
				ON t.id_transferencia = tp.id_transferencia
				LEFT JOIN ec_movimiento_almacen ma
				ON ma.id_transferencia = t.id_transferencia
				WHERE t.id_transferencia IN( {$transfers} )
				AND tp.id_producto_or IN( {$product_id} )
				ORDER BY SUM( ( tp.cantidad - tp.total_piezas_validacion ) ) DESC
				LIMIT 1";
		//return $sql;
		$stm = $link->query( $sql ) or die( "Error al consultar en que transferencia esta el producto : " . $link->error );
	//vuelve a validar que el producto exista en alguna transferencia
		if( $stm->num_rows <= 0 ){
			die( "error|<h5>El producto no pertence a ninguna Transferencia <br /> Aparte el producto de la transferencia para que no sea enviado a la sucursal</h5>" );
		}
		$transf = $stm->fetch_assoc();
		$transfer_id = $transf['transfer_id'];
		$mov_id = $transf['mov_id'];

	//inserta el detalle en transferencia producto
		$sql = "INSERT INTO ec_transferencia_productos( /*1*/id_transferencia, /*2*/id_producto_or, 
			/*3*/id_presentacion, /*4*/cantidad_presentacion, /*5*/cantidad, /*6*/id_producto_de, 
			/*7*/referencia_resolucion, /*8*/cantidad_cajas, /*9*/cantidad_paquetes, 
			/*10*/cantidad_piezas, /*11*/id_proveedor_producto, /*12*/cantidad_cajas_surtidas,
			/*13*/cantidad_paquetes_surtidos, /*14*/cantidad_piezas_surtidas, 
			/*15*/total_piezas_surtimiento, /*16*/cantidad_cajas_validacion, 
			/*17*/ cantidad_paquetes_validacion, /*18*/ cantidad_piezas_validacion, 
			/*19*/total_piezas_validacion, /*20*/agregado_surtimiento_validacion )
			SELECT
			/*1*/'{$transfer_id}',
			/*2*/'{$product_id}',
			/*3*/-1,
			/*4*/( pp.presentacion_caja * {$box} ) 
					+ ( pp.piezas_presentacion_cluces * {$pack} ) 
					+ {$piece} ,
			/*5*/( pp.presentacion_caja * {$box} ) 
					+ ( pp.piezas_presentacion_cluces * {$pack}) 
					+ {$piece} ,
			/*6*/'{$product_id}',
			/*7*/( pp.presentacion_caja * {$box} ) 
					+ ( pp.piezas_presentacion_cluces * {$pack} ) 
					+ {$piece},
			/*8*/'{$box}',
			/*9*/'{$pack}',
			/*10*/'{$piece}',
			/*11*/'{$product_provider_id}',
			/*12*/'{$box}',
			/*13*/'{$pack}',
			/*14*/'{$piece}',
			/*15*/( pp.presentacion_caja * {$box} ) 
					+ ( pp.piezas_presentacion_cluces * {$pack} ) 
					+ {$piece},
			/*16*/'{$box}',
			/*17*/'{$pack}',
			/*18*/'{$piece}',
			/*19*/( pp.presentacion_caja * {$box} ) 
					+ ( pp.piezas_presentacion_cluces * {$pack} ) 
					+ {$piece},
			/*20*/'1'
			FROM ec_proveedor_producto pp
			WHERE pp.id_proveedor_producto = '{$product_provider_id}'";
		$stm = $link->query( $sql ) or die( "Error al insertar el nuevo registro en la transferencia" . $link->error );
		$new_detail_id  = $link->insert_id;
	//inserta el detalle del movimiento de almacen
		$sql = "INSERT INTO ec_movimiento_detalle(id_movimiento, id_producto,cantidad,cantidad_surtida, 
				id_pedido_detalle, id_oc_detalle, id_proveedor_producto )
				SELECT 
					'{$mov_id}',
					tp.id_producto_or,
					tp.cantidad,
					tp.cantidad,
					-1,
					-1, 
					tp.id_proveedor_producto
				FROM ec_transferencia_productos tp
				WHERE tp.id_transferencia_producto = '{$new_detail_id}'";
		$stm = $link->query( $sql )or die( "Error al insertar el detalle del movimiento de almacen : " . $link->error );
		return "El producto fue agregado y validado exitosamente!";
	}

	function getInventoryAdjudments( $user, $link ){
		$resp = '';
		$sql = "SELECT 
					dipp.id_diferencia_inventario AS row_id,
					dipp.id_producto AS product_id,
					dipp.id_proveedor_producto AS product_provider_id,
					p.nombre AS name,
					pp.clave_proveedor AS provider_clue,
					ipp.inventario AS virual_inventory,
					IF( ppua.id_ubicacion_matriz IS NULL, 
						'No hay ubicaciones registradas',
						GROUP_CONCAT( 
							CONCAT( 
								IF( ppua.letra_pasillo_de = '', '', ppua.letra_pasillo_de ),
								IF( ppua.numero_pasillo_de = '', '', CONCAT( '-', ppua.numero_pasillo_de ) ),
								IF( ppua.letra_pasillo_a = '', '', CONCAT( ' a ', ppua.letra_pasillo_a ) ),
								IF( ppua.numero_pasillo_a = '', '', CONCAT( '-', ppua.numero_pasillo_a ) ),
								IF( ppua.fila_de = '', '', CONCAT( ', f', ppua.fila_de ) ),
								IF( ppua.fila_a = '', '', CONCAT( '-', ppua.fila_a ) ),
								IF( ppua.altura_de = '', '', CONCAT( ', n', ppua.altura_de ) ),
								IF( ppua.altura_a = '', '', CONCAT( '-', ppua.altura_a ) )
							)
							SEPARATOR '~' 
						)
					) AS locations
				FROM ec_diferencias_inventario_proveedor_producto dipp
				LEFT JOIN ec_productos p
				ON p.id_productos = dipp.id_producto
				LEFT JOIN ec_proveedor_producto pp
				ON pp.id_proveedor_producto = dipp.id_proveedor_producto
				LEFT JOIN ec_inventario_proveedor_producto ipp
				ON ipp.id_producto = dipp.id_producto
				AND ipp.id_proveedor_producto = dipp.id_proveedor_producto
				LEFT JOIN ec_proveedor_producto_ubicacion_almacen ppua
				ON ppua.id_proveedor_producto = pp.id_proveedor_producto
				WHERE ipp.id_almacen = 1
				AND dipp.ajustado = '0'
				AND dipp.id_usuario_resuelve = '{$user}'
				GROUP BY dipp.id_proveedor_producto";
	//die( $sql );
		$stm = $link->query( $sql ) or die( "Error al consultar los ajustes pendientes de realizar : {$link->error}" );
		if( $stm->num_rows <= 0 ){
			$resp = 'ok';
		}else{
			$resp = '<div class="row adjustments_list">';
				$resp .= '<div class="col-12">';
					$resp .= '<h5 class="orange">Para continuar es necesario hacer el ajuste de los';
					$resp .= ' siguientes inventarios : </h5>';
					//$resp .= '';
					$resp .= '<div class="adjudments_container">';
					$resp .= '<table class="table table-striped table-bordered table_70">';
						$resp .= '<thead class="list_header_sticky">';
							$resp .= '<tr>';
								$resp .= '<th width="25%">Producto</th>';
								$resp .= '<th width="25%">Modelo</th>';
								$resp .= '<th width="20%">Inv. Virtual</th>';
								$resp .= '<th width="20%">Inv. Físico</th>';
								$resp .= '<th width="10%">Ubic</th>';
							$resp .= '</tr>';
						$resp .= '</thead>';
						$resp .= '<tbody id="inventoryAdjudments">';
					$counter = 0;
					while ( $row = $stm->fetch_assoc() ) {
							$resp .= '<tr ">';
								$resp .= '<td id="adjustment_1_' . $counter . '" class="no_visible">' . $row['row_id'] .' </td>';
								$resp .= '<td id="adjustment_2_' . $counter . '" class="no_visible">' . $row['product_id'] .' </td>';
								$resp .= '<td id="adjustment_3_' . $counter . '" class="no_visible">' . $row['product_provider_id'] .' </td>';
								$resp .= '<td style="vertical-align : middle;" id="adjustment_4_' . $counter . '">' . $row['name'] .' </td>';
								$resp .= '<td style="vertical-align : middle;" id="adjustment_5_' . $counter . '">' . $row['provider_clue'] .' </td>';
								$resp .= '<td style="vertical-align : middle;" id="adjustment_6_' . $counter . '">' . $row['virual_inventory'] .' </td>';
								$resp .= '<td style="vertical-align : middle;"><input id="adjustment_7_' . $counter . '" type="number" class="form-control"';
								$resp .= ' onchange="calculate_adjustment_differece( ' . $counter . ' );"></td>';
								$resp .= '<td id="adjustment_8_' . $counter . '" class="no_visible">0</td>';
								$resp .= '<td id="adjustment_9_' . $counter . '" class="no_visible">' . $row['locations'] . '</td>';
								$resp .= '<td style="vertical-align : middle;">';
									$resp .= '<button onclick="sow_adjustemt_locations( ' . $counter .  ' );" class="btn-info">';
										$resp .= '<i class="icon-location"></i>';
									$resp .= '</button>';
								$resp .= '</td>';
							$resp .= '</tr>';
						$counter ++;
					}
						$resp .= '</tbody>';
					$resp .= '</table>';
					$resp .= '</div>';
				//$resp .= '</div>';
			$resp .= '</div><br><br>';

			$resp .= '<div class="row adjudments_buttons" style="margin-top : 40px;">';
				$resp .= '<div class="col-1"></div>';//
				$resp .= '<div class="col-10">';//adjudments_buttons
					$resp .= '<button type="button" class="btn btn-success form-control"';
					$resp .= ' onclick="save_adjustment();">';
						$resp .= '<i class="icon-ok-circle">Guardar Ajuste</i>';
					$resp .= '</button><br><br>';
					$resp .= '<input type="password" class="form-control" placeholder="Password de encargado">';
					$resp .= '<br><button type="button" class="btn btn-warning form-control">';
						$resp .= '<i class="">Omitir ajuste</i>';
					$resp .= '</button>';
				$resp .= '</div>';
			$resp .= '</div>';
		}
		return $resp;
	}

//-8, +9
	function inventoryAdjustment( $addition, $substraction, $data_ok, $user, $link ){
		$resp = '';
		$link->autocommit( false );
		if( $substraction != '' &&  $substraction != null  ){
	//inserta la cabecera del movimiento de almacen ( resta )
			$sql = "INSERT INTO ec_movimiento_almacen ( /*1*/id_movimiento_almacen, /*2*/id_tipo_movimiento, 
				/*3*/id_usuario, /*4*/id_sucursal, /*5*/fecha, /*6*/hora, /*7*/observaciones, /*8*/id_pedido,
				/*9*/id_orden_compra, /*10*/lote, /*11*/id_maquila, /*12*/id_transferencia, /*13*/id_almacen )
					VALUES( /*1*/NULL, /*2*/8, /*3*/{$user}, /*4*/1, /*5*/NOW(), /*6*/NOW(), 
						/*7*/'RESTA POR AJUSTE DE INVENTARIO DESDE VALIDACIÓN', /*8*/-1, /*9*/-1, /*10*/NULL,
						/*11*/-1, /*12*/-1, /*13*/1 )";
			$stm = $link->query( $sql ) or die( "Error al insertar cabecera de movimiento de almacen ( ajuste ): {$link->error}" );
			$mov_header_id = (int) $link->insert_id;
			$substraction_array = explode( '|', $substraction );
			//die( $substraction );
			foreach ( $substraction_array as $key => $sub ) {
				$sub = explode( '~', $sub );
				if( $sub[0] != '' && $sub[0] != null ){
					$sql = "INSERT INTO ec_movimiento_detalle ( /*1*/id_movimiento_almacen_detalle, /*2*/id_movimiento,
						/*3*/id_producto, /*4*/cantidad, /*5*/cantidad_surtida, /*6*/id_pedido_detalle,/*7*/id_oc_detalle,
						/*8*/id_proveedor_producto ) VALUES ( /*1*/NULL, /*2*/{$mov_header_id},/*3*/{$sub[1]}, /*4*/{$sub[3]}, 
						/*5*/{$sub[3]}, /*6*/-1, /*7*/-1, /*8*/{$sub[2]} )";
					$exc = $link->query( $sql ) or die ( "Error al insertar el detalle del movimiento de almacen 1 : {$link->error}" );	
					
					$sql = "UPDATE ec_diferencias_inventario_proveedor_producto
								SET ajustado = '1' WHERE id_diferencia_inventario = {$sub[0]}";
					$exc = $link->query( $sql ) or die( "Error al actualizar el registro de ajuste de inventario 1 : {$link->error} {$sql}" );			
				}
		//die( $sql );
			}
		}


		if( $addition != '' &&  $addition != null  ){
	//inserta la cabecera del movimiento de almacen ( suma )
			$sql = "INSERT INTO ec_movimiento_almacen ( /*1*/id_movimiento_almacen, /*2*/id_tipo_movimiento, 
				/*3*/id_usuario, /*4*/id_sucursal, /*5*/fecha, /*6*/hora, /*7*/observaciones, /*8*/id_pedido,
				/*9*/id_orden_compra, /*10*/lote, /*11*/id_maquila, /*12*/id_transferencia, /*13*/id_almacen )
					VALUES( /*1*/NULL, /*2*/9, /*3*/{$user}, /*4*/1, /*5*/NOW(), /*6*/NOW(), 
						/*7*/'SUMA POR AJUSTE DE INVENTARIO DESDE VALIDACIÓN', /*8*/-1, /*9*/-1, /*10*/NULL,
						/*11*/-1, /*12*/-1, /*13*/1 )";
			$stm = $link->query( $sql ) or die( "Error al insertar cabecera de movimiento de almacen ( ajuste ): {$link->error}" );
			$mov_header_id = (int) $link->insert_id;
			$addition_array = explode( '|', $addition );
			foreach ( $addition_array as $key => $add ) {
				$add = explode( '~', $add );
				if( $add[0] != '' && $add[0] != null ){
					$sql = "INSERT INTO ec_movimiento_detalle ( /*1*/id_movimiento_almacen_detalle, /*2*/id_movimiento,
						/*3*/id_producto, /*4*/cantidad, /*5*/cantidad_surtida, /*6*/id_pedido_detalle,/*7*/id_oc_detalle,
						/*8*/id_proveedor_producto ) VALUES ( /*1*/NULL, /*2*/{$mov_header_id},/*3*/{$add[1]}, /*4*/{$add[3]}, 
						/*5*/{$add[3]}, /*6*/-1, /*7*/-1, /*8*/{$add[2]} )";
					$exc = $link->query( $sql) or die( "Error al insertar el detalle del movimiento de almacen 2 : {$link->error}" );	
					
					$sql = "UPDATE ec_diferencias_inventario_proveedor_producto
								SET ajustado = '1' WHERE id_diferencia_inventario = {$add[0]}";
					$exc = $link->query( $sql ) or die( "Error al actualizar el registro de ajuste de inventario 2 : {$link->error}" );			
				}
			}
		}

		$ok_array = explode( '|', $addition );
		foreach ( $ok_array as $key => $ok ) {
			if( $ok[0] != '' && $ok[0] != null ){
				$ok = explode( '~', $ok );
				$sql = "UPDATE ec_diferencias_inventario_proveedor_producto
							SET ajustado = '1' WHERE id_diferencia_inventario = {$ok[0]}";
				$exc = $link->query( $sql ) or die( "Error al actualizar el registro de ajuste de inventario 3 : {$link->error}" );			
			}
		}

		$link->autocommit( true );

		$resp = '<h5 style="color : green;">Ajuste de inventario guardado exitosamente!</h5>';
		$resp .= '<div class="row">';
			$resp .= '<div class="col-2"></div>';
			$resp .= '<div class="col-8">';
				$resp .= '<button type="button" class="btn btn-success" onclick="location.reload();">';
					$resp .= '<i class="icon-ok-circle">Aceptar</i>';
				$resp .= '</button>';
			$resp .= '</div>';
		$resp .= '</div>';
		return $resp;
	}

	function seekByName( $barcode, $link ){
		$barcode_array = explode(' ', $barcode );
		$condition = " OR (";
		foreach ($barcode_array as $key => $barcode_txt ) {
			$condition .= ( $condition == ' OR (' ? '' : ' AND' );
			$condition .= " p.nombre LIKE '%{$barcode_txt}%'";
		}
		$condition .= " )";
		$sql = "SELECT
				pp.id_producto AS product_id,
				CONCAT( p.nombre, ' <b>( ', GROUP_CONCAT( pp.clave_proveedor SEPARATOR ', ' ), ' ) </b>' ) AS name
			FROM ec_productos p
			LEFT JOIN ec_proveedor_producto pp
			ON pp.id_producto = p.id_productos
			WHERE ( pp.clave_proveedor LIKE '%{$barcode}%'
			{$condition} ) AND pp.id_proveedor_producto IS NOT NULL
			GROUP BY p.id_productos";
		$stm_name = $link->query( $sql ) or die( "error|error al consultar coincidencias por nombre / modelo : {$link->error}" );
		if( $stm_name->num_rows <= 0 ){
			return 'exception|<br/><h3 class="inform_error">El código de barras no esta registrado en ningún producto, tampoco coincide ningún nombre / modelo de Producto </h3>' 
			. '<div class="row"><div class="col-2"></div><div class="col-8">'
			. '<button class="btn btn-danger form-control" onclick="close_emergent( \'#barcode_seeker\', \'#barcode_seeker\' );">Aceptar</button></div><br/><br/>';
		}

		$resp = "seeker|";
		while ( $row_name = $stm_name->fetch_assoc() ) {
			$resp .= "<div class=\"group_card\" onclick=\"setProductByName( {$row_name['product_id']} );\">";
				$resp .= "<p>{$row_name['name']}</p>";
			$resp .= "</div>";
		}
		//echo $resp;
		return $resp;
	}
	function getOptionsByProductId( $product_id, $link ){
		$sql = "SELECT
					pp.id_proveedor_producto AS product_provider_id,
					pp.clave_proveedor AS provider_clue,
					pp.piezas_presentacion_cluces AS pack_pieces,
					pp.presentacion_caja AS box_pieces,
					ipp.inventario AS inventory,
					pp.codigo_barras_pieza_1 AS piece_barcode_1
				FROM ec_proveedor_producto pp
				LEFT JOIN ec_inventario_proveedor_producto ipp
				ON ipp.id_producto = pp.id_producto 
				AND ipp.id_proveedor_producto = pp.id_proveedor_producto
				WHERE pp.id_producto = {$product_id}
				AND ipp.id_almacen = 1";
		$stm_name = $link->query( $sql ) or die( "error|Error al consutar el detalle del producto : {$link->error}" ); 
		$resp = "<div class=\"row\">";
			//$resp .= "<div class=\"col-2\"></div>";
			$resp .= "<div class=\"col-12\">";
				$resp .= "<h5>Seleccione el modelo del producto : </h5>";
				$resp .= "<table class=\"table table-bordered table-striped table_70\">";
				$resp .= "<thead>
							<tr>
								<th>Modelo</th>
								<th>Inventario</th>
								<th>Pzs x caja</th>
								<th>Pzs x paquete</th>
								<th>Seleccionar</th>
							</tr>
						</thead><tbody id=\"model_by_name_list\" >";
				$counter = 0;
				while( $row_name = $stm_name->fetch_assoc() ){
					$resp .= "<tr>";
						$resp .= "<td id=\"p_m_1_{$counter}\" align=\"center\">{$row_name['provider_clue']}</td>";
						$resp .= "<td id=\"p_m_2_{$counter}\" align=\"center\">{$row_name['inventory']}</td>";
						$resp .= "<td id=\"p_m_3_{$counter}\" align=\"center\">{$row_name['box_pieces']}</td>";
						$resp .= "<td id=\"p_m_4_{$counter}\" align=\"center\">{$row_name['pack_pieces']}</td>";
						$resp .= "<td align=\"center\"><input type=\"radio\" id=\"p_m_5_{$counter}\" 
							value=\"{$row_name['piece_barcode_1']}\"  name=\"search_by_name_selection\"></td>";
					$resp .= "</tr>";
					$counter ++;
				}
				$resp .= "</tbody></table>";
			$resp .= "</div>";
			$resp .= "<div class=\"col-2\"></div>";
			$resp .= "<div class=\"col-8\">
						<button class=\"btn btn-success form-control\" onclick=\"setProductModel();\">
							<i class=\"icon-ok-circle\">Continuar</i>
						</button><br><br>
						<button class=\"btn btn-danger form-control\"
							onclick=\"close_emergent( '#barcode_seeker', '#barcode_seeker' );\">
							<i class=\"icon-ok-circle\">Cancelar</i>
						</button>
					</div>";
		$resp .= "</div>";
		return $resp;
	}

	function getBarcodesTypes( $link ){
		$sql = "SELECT 
					omitir_codigos_barras_unicos AS skip_unique_barcodes
				FROM sys_configuracion_sistema";
		$stm = $link->query( $sql ) or die( "Error al consultar configuración de códigos de barras : {$link->error}" );
		$row = $stm->fetch_assoc();
		return "<input type=\"hidden\" id=\"skip_unique_barcodes\" value=\"{$row['skip_unique_barcodes']}\">";
	}

	function seekRecivedProducts( $txt, $transfers, $link ){
		$array_txt = explode(' ', $txt );

		$condition = " AND ( (";
		foreach ($array_txt as $key => $word) {
			$condition .= ( $key > 0 ? ' AND' : '' );
			$condition .= " p.nombre LIKE '%{$word}%'";
		}
		$condition .= " ) OR p.clave LIKE '%{$txt}%' OR p.orden_lista LIKE '%{$txt}%' 
					OR pp.codigo_barras_pieza_1 = '{$txt}'
					OR pp.codigo_barras_pieza_2 = '{$txt}'
					OR pp.codigo_barras_pieza_3 = '{$txt}'
					OR pp.codigo_barras_presentacion_cluces_1  = '{$txt}'
					OR pp.codigo_barras_presentacion_cluces_2 = '{$txt}'
					OR pp.codigo_barras_caja_1 = '{$txt}'
					OR pp.codigo_barras_caja_2 = '{$txt}')";

		$sql = "SELECT
					CONCAT( p.nombre, 
						' <b>(Modelo ' , pp.clave_proveedor , ')</b> <b>(', 
						SUM( ( tvu.cantidad_cajas_validadas * pp.presentacion_caja ) 
							+ ( tvu.cantidad_paquetes_validados * pp.piezas_presentacion_cluces )
							+ tvu.cantidad_piezas_validadas
						),
						' piezas validadas)</b>' 
					)AS name,
					tvu.id_producto AS product_id,
					tvu.id_proveedor_producto
				FROM ec_transferencias_validacion_usuarios tvu
				LEFT JOIN ec_transferencia_productos tp
				ON tp.id_producto_or = tvu.id_producto
				LEFT JOIN ec_productos p 
				ON tp.id_producto_or = p.id_productos
				LEFT JOIN ec_proveedor_producto pp
				ON tp.id_proveedor_producto = pp.id_proveedor_producto
				WHERE tp.id_transferencia IN( {$transfers} )
				{$condition}
				GROUP BY tp.id_proveedor_producto";
		$stm = $link->query( $sql ) or die( "Error al consultar coincidencias de productos :recibidos {$link->error} {$sql}" );
		if( $stm->num_rows <= 0 ){
			return "<div class=\"response_recived\">Sin coincidencias!</div>";
		}
		while( $r = $stm->fetch_assoc() ){
			$resp .= "<div class=\"response_recived\" onclick=\"load_product_validation_detail( this, {$r['product_id']} );\">{$r['name']}</div>";
		}
		return $resp;
	}

	function loadProductValidationDetail( $product_id, $transfers, $link ){
		
		$sql = "SELECT
					/*tvu.id_transferencia_validacion AS transfer_validation_id,*/
					p.id_productos AS product_id,
					CONCAT( p.nombre, ' ( MODELO : <b>', pp.clave_proveedor, '</b> )' ) AS name,
					t.id_transferencia AS transfer,
					IF(	tvu.cantidad_cajas_validadas > 0, 
						CONCAT( tvu.cantidad_cajas_validadas, ' caja', IF( tvu.cantidad_cajas_validadas > 1, 's', '' )),
						IF( tvu.cantidad_paquetes_validados > 0,
							CONCAT( tvu.cantidad_paquetes_validados, ' paquete', IF( tvu.cantidad_cajas_validadas > 1, 's', '' )),
							CONCAT( tvu.cantidad_piezas_validadas, ' pieza', IF( tvu.cantidad_piezas_validadas > 1, 's', '' ))
						)
					) AS recived
				FROM ec_transferencias_validacion_usuarios tvu
				LEFT JOIN ec_transferencia_productos tp 
				ON tvu.id_transferencia_producto = tp.id_transferencia_producto
				LEFT JOIN ec_transferencias t ON tp.id_transferencia = t.id_transferencia
				LEFT JOIN ec_productos p ON tvu.id_producto = p.id_productos
				LEFT JOIN ec_proveedor_producto pp 
				ON tvu.id_proveedor_producto = pp.id_proveedor_producto
				WHERE t.id_transferencia IN( {$transfers} )
				/*AND tvu.id_usuario = '{$user}'*/
				AND tvu.id_producto = {$product_id}
				GROUP BY tvu.id_transferencia_validacion
				ORDER BY tvu.id_transferencia_validacion DESC";
		$stm = $link->query( $sql ) or die( "Error al consultar detalles de surtimiento de producto : {$link->error}" ); 
		//return $sql;
		return buildLastValidations( $stm );
	}

	function makeTransfersGroup( $transfers, $link ){
		$link->autocommit( false );
	//agrupa y consulta las transferencias que ya están en un grupo
		$sql = "SELECT 
					t.id_transferencia AS transfer_id
				FROM ec_transferencias t
				LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
				ON btvd.id_transferencia = t.id_transferencia
				WHERE btvd.id_transferencia IS NULL
				AND t.id_transferencia IN( {$transfers} )";
		$stm_block = $link->query( $sql ) or die( "Error al consultar bloques : {$link->error}" );
		if( $stm_block->num_rows > 0 ){
		//inserta cabecera del bloque
			$sql = "INSERT INTO ec_bloques_transferencias_validacion ( id_bloque_transferencia_validacion, fecha_alta, validado )
			VALUES( NULL, NOW(), 0 )";	
			$stm = $link->query( $sql ) or die( "Error al insertar cabecera del bloque : {$link->error}" );
			$header_id = $link->insert_id;
			$arr_transfers = explode( ',', $transfers );
		//inserta detalles del bloque
			while( $row = $stm_block->fetch_assoc() ){
				//foreach ( $arr_transfers as $key => $transfer ) {
				$sql = "INSERT INTO ec_bloques_transferencias_validacion_detalle ( id_bloque_transferencia_validacion_detalle, id_bloque_transferencia_validacion, 
				id_transferencia, fecha_alta, invalidado ) VALUES ( NULL, {$header_id}, {$row['transfer_id']}, NOW(), 0 )";
				$stm = $link->query( $sql ) or die("Error al insertar el detalle del bloque de validación : {$link->error}");	
				//}
			}	
		}
		$link->autocommit( true );
		return 'ok';
	}

	function  getPreviousRemoveTransferToValidation( $transfer_id, $reset_all = null, $link ){
		$resp = "";
		$resp .= "<div class=\"col-12\">";
		if( $reset_all != null ){
			$resp .= "<p align=\"justify\" style=\"color:red;\">Se va a resetear la validación de la transferencia, si ya hay productos que tenia por enviar, regresalos a Matriz</p>";
		}else{
			$change_products = "";
			$no_exists_products = "";
			$no_exists_products_provider = "";
		//consulta el bloque al que corresponde
			$sql = "SELECT 
						btvd.id_bloque_transferencia_validacion AS block_id,
						btvd.id_bloque_transferencia_validacion_detalle AS block_detail_id
					FROM ec_bloques_transferencias_validacion_detalle btvd
					WHERE btvd.id_transferencia = {$transfer_id}";
			$stm_1 = $link->query( $sql ) or die( "Error al consultar el bloque de la validadcion de la transferencia : {$link->error}" );
			$row_block = $stm_1->fetch_assoc();
		//verifica si fue recibido algo en esta transferencia
			$sql = "SELECT 
						CONCAT( p.nombre , ' <b>(MODELO : ', pp.clave_proveedor, ')</b> ' ) AS name,
						tp.id_transferencia_producto AS transfer_product_id,
						tp.id_producto_or AS product_id,
						tp.id_proveedor_producto AS product_provider_id,
						tp.cantidad_cajas_validacion AS validated_boxes,
						tp.cantidad_paquetes_validacion AS validated_packs,
						tp.cantidad_piezas_validacion AS validated_pieces,
						tp.total_piezas_validacion AS validated_total
					FROM ec_transferencia_productos tp
					LEFT JOIN ec_productos p
					ON p.id_productos = tp.id_producto_or
					LEFT JOIN ec_proveedor_producto pp 
					ON pp.id_proveedor_producto = tp.id_proveedor_producto
					WHERE tp.id_transferencia IN( {$transfer_id} )
					AND ( tp.cantidad_cajas_validacion > 0 OR tp.cantidad_paquetes_validacion > 0
						OR tp.cantidad_piezas_validacion > 0 OR tp.total_piezas_validacion > 0 )";
			$stm_2 = $link->query( $sql ) or die( "Error al consultar los detalles que fueroin validados en la transferencia por quitar : {$link->error}"  );
			if( $stm_2->num_rows > 0 ){
				while( $row_validated = $stm_2->fetch_assoc() ){
					$sql = "SELECT 
								tp.id_transferencia_producto
							FROM ec_transferencia_productos tp
							LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
							ON tp.id_transferencia = btvd.id_transferencia 
							LEFT JOIN ec_bloques_transferencias_validacion btv
							ON btv.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
							WHERE btvd.id_transferencia = {$row_block['block_id']}
							AND tp.id_transferencia NOT IN( {$transfer_id} )
							AND tp.id_proveedor_producto = {$row_validated['transfer_product_id']}
							AND tp.id_producto_or = {$row_validated['product_id']}";
					$stm_3 = $link->query( $sql ) or die( "Error al consultar las transferencias que contienen al proveedor-producto : {$link->error}" );
					if( $stm3->num_rows <= 0 ){
					//verifica si el producto existe en alguna transferencia del bloque
						$sql = "SELECT 
									tp.id_transferencia_producto
								FROM ec_transferencia_productos tp
								LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
								ON tp.id_transferencia = btvd.id_transferencia 
								LEFT JOIN ec_bloques_transferencias_validacion btv
								ON btv.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
								WHERE btvd.id_transferencia = {$row_block['block_id']}
								AND tp.id_transferencia NOT IN( {$transfer_id} )
								AND tp.id_producto_or = {$row_validated['product_id']}";
						$stm_4 = $link->query( $sql ) or die( "Error al consultar las transferencias que contienen al producto : {$link->error}" );
						if( $stm_4->num_rows <= 0 ){
						//crea el registro informativo de asignación
							$no_exists_products .= "<div class=\"group_card\" id=\"detail_{$row_validated['transfer_product_id']}\">";
								$no_exists_products .= "<div class=\"row\">";
									$no_exists_products .= "<div class=\"col-9\">";				
										$no_exists_products .= "<p><b class=\"orange\">Producto : </b> {$row_validated['name']}</p>";
									$no_exists_products .= "</div>";	
									$no_exists_products .= "<div class=\"col-3\">";	
										$no_exists_products .= "<button type=\"button\" class=\"btn btn-danger\"
																onclick=\"removeTransferBlockDetail( {$row_validated['transfer_product_id']} );\">
																	<i class=\"icon-cancel-alt-filled\"></i>
															</button>";	
									$no_exists_products .= "</div>";	
									$no_exists_products .= "<div class=\"col-6\">";	
										$no_exists_products .= "<p><b class=\"orange\">Cajas : </b>{$row_validated['validated_boxes']}</p>";
									$no_exists_products .= "</div>";	
									$no_exists_products .= "<div class=\"col-6\">";	
										$no_exists_products .= "<p><b class=\"orange\">Paquetes : </b>{$row_validated['validated_packs']}</p>";
									$no_exists_products .= "</div>";	
									$no_exists_products .= "<div class=\"col-6\">";	
										$no_exists_products .= "<p><b class=\"orange\">Piezas : </b>{$row_validated['validated_pieces']}</p>";
									$no_exists_products .= "</div>";	
									$no_exists_products .= "<div class=\"col-6\">";	
										$no_exists_products .= "<p><b class=\"orange\">Total : </b>{$row_validated['validated_total']}</p>";
									$no_exists_products .= "</div>";	
								$no_exists_products .= "</div>";
									//$resp .= "<p><b class=\"orange\">Piezas validadas :</b>{$row_validated['validated_total']}</p>";
							$no_exists_products .= "</div>";
						}else{
							$row_validated_1 = $stm_4->fetch_assoc();
						//crea el registro informativo de asignación
							$no_exists_products_provider .= "<div class=\"group_card\" id=\"detail_{$row_validated['transfer_product_id']}\">";
								$no_exists_products_provider .= "<div class=\"row\">";
									$no_exists_products_provider .= "<div class=\"col-9\">";				
										$no_exists_products_provider .= "<p><b class=\"orange\">Producto : </b> {$row_validated_1['name']}</p>";
									$no_exists_products_provider .= "</div>";	
									$no_exists_products .= "<div class=\"col-3\">";		
										$no_exists_products_provider .= "<button type=\"button\" class=\"btn btn-danger\"
																onclick=\"removeTransferBlockDetail( {$row_validated['transfer_product_id']} );\">
																	<i class=\"icon-cancel-alt-filled\"></i>
															</button>";	
									$no_exists_products .= "</div>";	
									$no_exists_products_provider .= "<div class=\"col-6\">";	
										$no_exists_products_provider .= "<p><b class=\"orange\">Cajas : </b>{$row_validated_1['validated_boxes']}</p>";
									$no_exists_products_provider .= "</div>";	
									$no_exists_products_provider .= "<div class=\"col-6\">";	
										$no_exists_products_provider .= "<p><b class=\"orange\">Paquetes : </b>{$row_validated_1['validated_packs']}</p>";
									$no_exists_products_provider .= "</div>";	
									$no_exists_products_provider .= "<div class=\"col-6\">";	
										$no_exists_products_provider .= "<p><b class=\"orange\">Piezas : </b>{$row_validated_1['validated_pieces']}</p>";
									$no_exists_products_provider .= "</div>";	
									$no_exists_products_provider .= "<div class=\"col-6\">";	
										$no_exists_products_provider .= "<p><b class=\"orange\">Total : </b>{$row_validated_1['validated_total']}</p>";
									$no_exists_products_provider .= "</div>";	
								$no_exists_products_provider .= "</div>";
									//$resp .= "<p><b class=\"orange\">Piezas validadas :</b>{$row_validated['validated_total']}</p>";
							$no_exists_products_provider .= "</div>";
						}
					}else{
					//crea el registro informativo de asignación
						$change_products .= 'here';
						$change_products .= "<div class=\"group_card\" id=\"detail_{$row_validated['transfer_product_id']}\">";
							$change_products .= "<div class=\"row\">";
								$change_products .= "<div class=\"col-9\">";				
									$change_products .= "<p><b class=\"orange\">Producto : </b> {$row_validated['name']}</p>";
								$change_products .= "</div>";		
								$change_products .= "<div class=\"col-3\">";		
									$change_products .= "<button type=\"button\" class=\"btn btn-danger\"
															onclick=\"removeTransferBlockDetail( {$row_validated['transfer_product_id']} );\">
																<i class=\"icon-cancel-alt-filled\"></i>
														</button>";	
								$change_products .= "</div>";	
								$change_products .= "<div class=\"col-6\">";	
									$change_products .= "<p><b class=\"orange\">Cajas : </b>{$row_validated['validated_boxes']}</p>";
								$change_products .= "</div>";	
								$change_products .= "<div class=\"col-6\">";	
									$change_products .= "<p><b class=\"orange\">Paquetes : </b>{$row_validated['validated_packs']}</p>";
								$change_products .= "</div>";	
								$change_products .= "<div class=\"col-6\">";	
									$change_products .= "<p><b class=\"orange\">Piezas : </b>{$row_validated['validated_pieces']}</p>";
								$change_products .= "</div>";	
								$change_products .= "<div class=\"col-6\">";	
									$change_products .= "<p><b class=\"orange\">Total : </b>{$row_validated['validated_total']}</p>";
								$change_products .= "</div>";	
							$change_products .= "</div>";
								//$resp .= "<p><b class=\"orange\">Piezas validadas :</b>{$row_validated['validated_total']}</p>";
						$change_products .= "</div>";
					}
					//}
				}
				if( $change_products != '' ){
					$resp .= "<h5 class=\"orange\" style=\"color : green;\">Estos productos ya fueron validados y serán asignados a las demás transferencias del bloque : </h5>";
					$resp .= $change_products;
				}
				if( $no_exists_products_provider != '' ){
					$resp .= "<h5 class=\"orange\">Estos productos ya fueron validados, el modelo no fue pedido y serán asignados a las demás transferencias del bloque : </h5>";
					$resp .= $no_exists_products_provider;
				}
				if( $no_exists_products != '' ){
					$resp .= "<h5 class=\"orange\" style=\"color : red;\">Estos productos ya fueron validados y no  estaban contemplados en las transferencias serán asignados a las demás transferencias del bloque : </h5>";
					$resp .= $no_exists_products;
				}
			}else{
				$resp .= "<p class=\"green\">No se validó ningún producto en esta transferencia <i class=\"icon-ok-circle\"></i></p>";
			}
		}
			
		$resp .= "<div class=\"row\">";
			$resp .= "<div class=\"col-2\"></div>";
			$resp .= "<div class=\"col-8\">
						<label for=\"manager_password\">Pida al encargado que ingrese su contraseña <b class=\"orange\">*</b> : </label>
						<input type=\"password\" class=\"form-control\" id=\"manager_password\"><br>
						<button type=\"button\" class=\"btn btn-success form-control\" onclick=\"confirm_remove_transfer_block();\">
							<i class=\"icon-ok-circle\">Aceptar y continuar</i>
						</button><br><br>
						<button type=\"button\" class=\"btn btn-danger form-control\" onclick=\"close_emergent( )\">
							<i class=\"icon-cancel-circled\">Cancelar</i>
						</button><br><br><br><br>
					</div>";
		$resp .= "</div>";
			//$resp .= "<div class=\"col-1\"></div>";
		
		return $resp;
	}

	function removeTransferBlockDetail( $transfer_id, $transfer_product_id, $link ){

		$link->autocommit( false );

		/*$sql = "SELECT 
					id_bloque_transferencia_validacion 
				FROM ec_bloques_transferencias_validacion_detalle
				WHERE id_transferencia = {$transfer_id}";	
		$stm = $link->query( $sql ) or die( "Error al consultar id de bloque de validación : " . $link->error );*/
	//resetea toda la validacion
		/*$sql = "DELETE FROM ec_transferencia_codigos_unicos WHERE id_bloque_transferencia_validacion = '{$details_nums['block_id']}'";
		$stm_delete = $link->query( $sql ) or die( "Error al eliminar códigos únicos del bloque de transferencia : {$link->error}");*/
	//elimina los códigos único
		$sql = "DELETE FROM ec_transferencia_codigos_unicos 
				WHERE id_transferencia_validacion IN ( SELECT id_transferencia_validacion FROM ec_transferencias_validacion_usuarios WHERE id_transferencia_producto = {$transfer_product_id} )";
		$stm_delete = $link->query( $sql ) or die( "Error al eliminar códigos únicos de validación de transferencia : {$link->error}" );
		
		$sql = "DELETE FROM ec_transferencias_validacion_usuarios 
				WHERE id_transferencia_producto IN( {$transfer_product_id} )";
		$stm_delete = $link->query( $sql ) or die( "Error al eliminar detalles de validación de transferencia : {$link->error}") ;
		
		$sql = "UPDATE ec_transferencia_productos SET 
						cantidad_cajas_validacion = 0,
						cantidad_paquetes_validacion = 0,
						cantidad_piezas_validacion = 0,
						total_piezas_validacion = 0
				WHERE id_transferencia_producto = {$transfer_product_id}";
		$stm_update = $link->query( $sql ) or die( "Error al poner en cero las piezas validadas en el detalle de transferencia : {$link->error}" );

		$link->autocommit( true );
		$resp = "<div>";
			$resp .= "<div class=\"row\">";
			$resp .= "<div class=\"col-2\"></div>";
			$resp .= "<div class=\"col-8\">";
				$resp .= "<button type=\"button\" class=\"btn btn-succes\" onclick=\"close_emergent_2();\">
							<i class=\"icon-ok-circle\">Aceptar</i>
						</button>";
			$resp .= "</div>";
			$resp .= "</div>";
		$resp .= "</div>";
		return $resp;
	}

	function removeTransferBlock( $transfer_id, $link ){
		$resp = "";
		$link->autocommit( false );
		
		$sql = "SELECT 
					COUNT( btvd.id_bloque_transferencia_validacion_detalle ) AS counter,
					btv.id_bloque_transferencia_validacion AS block_id
				FROM ec_bloques_transferencias_validacion_detalle btvd
				LEFT JOIN ec_bloques_transferencias_validacion btv
				ON btv.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
				WHERE btv.id_bloque_transferencia_validacion 
				IN ( SELECT 
						id_bloque_transferencia_validacion 
					FROM ec_bloques_transferencias_validacion_detalle
					WHERE id_transferencia = {$transfer_id}
				)";
		$stm = $link->query( $sql ) or die( "Error al consultar cuantas transferencias conforman el bloque : {$link->error}");
		$details_nums = $stm->fetch_assoc();
		$num_block_details = $details_nums['counter'];
		if( $num_block_details <= 1 ){
		//resetea toda la validacion
			$sql = "DELETE FROM ec_transferencia_codigos_unicos WHERE id_bloque_transferencia_validacion = '{$details_nums['block_id']}'";
			$stm_delete = $link->query( $sql ) or die( "Error al eliminar códigos únicos del bloque de transferencia : {$link->error}");
			$sql = "DELETE FROM ec_transferencias_validacion_usuarios 
					WHERE id_transferencia_producto IN( SELECT id_transferencia_producto FROM ec_transferencia_productos WHERE id_transferencia = {$transfer_id})";
			$stm_delete = $link->query( $sql ) or die( "Error al eliminar detalles de validación de transferencia : {$link->error}");
			
			$sql = "DELETE FROM ec_bloques_transferencias_validacion_detalle WHERE id_bloque_transferencia_validacion = {$details_nums['block_id']}";
			$stm_delete = $link->query( $sql ) or die( "Error al eliminar detalle de bloque de transferencia : {$link->error}");
			
			$sql = "DELETE FROM ec_bloques_transferencias_validacion WHERE id_bloque_transferencia_validacion = {$details_nums['block_id']}";
			$stm_delete = $link->query( $sql ) or die( "Error al eliminar bloque de transferencia : {$link->error}");
			
			$sql = "UPDATE ec_transferencia_productos SET 
						cantidad_cajas_validacion = 0,
						cantidad_paquetes_validacion = 0,
						cantidad_piezas_validacion = 0,
						total_piezas_validacion = 0
					WHERE id_transferencia = {$transfer_id}";
			$stm_update = $link->query( $sql ) or die( "Error al resetear detalles de validación de productos de transferencia : {$link->error}");
			$sql = "UPDATE ec_transferencias SET id_estado = 4 WHERE id_transferencia = {$transfer_id} ";
			$stm_update = $link->query( $sql ) or die( "Error al actualizar la transferencia a Surtiendo y revisando : {$link->error}");
			
			$resp .= "<p align=\"center\" style=\"color : green;\">";
				$resp .= "La validación de la Transferencia fue reiniciada exitosamente!";
			$resp .= "</p>";

		}else{
		//si el bloque es de más de una transferencia
			$change_products = "";
			$no_exists_products = "";
			$no_exists_products_provider = "";
		//consulta el bloque al que corresponde
			$sql = "SELECT 
						btvd.id_bloque_transferencia_validacion AS block_id,
						btvd.id_bloque_transferencia_validacion_detalle AS block_detail_id
					FROM ec_bloques_transferencias_validacion_detalle btvd
					WHERE btvd.id_transferencia = {$transfer_id}";
			$stm_1 = $link->query( $sql ) or die( "Error al consultar el bloque de la validadcion de la transferencia : {$link->error}" );
			$row_block = $stm_1->fetch_assoc();
		//verifica si fue validado algo en la transferencia
			$sql = "SELECT 
						tp.id_transferencia_producto AS transfer_product_id,
						tp.id_producto_or AS product_id,
						tp.id_proveedor_producto AS product_provider_id,
						tp.cantidad_cajas_validacion AS validated_boxes,
						tp.cantidad_paquetes_validacion AS validated_packs,
						tp.cantidad_piezas_validacion AS validated_pieces,
						tp.total_piezas_validacion AS validated_total
					FROM ec_transferencia_productos tp
					LEFT JOIN ec_productos p
					ON p.id_productos = tp.id_producto_or
					LEFT JOIN ec_proveedor_producto pp 
					ON pp.id_proveedor_producto = tp.id_proveedor_producto
					WHERE tp.id_transferencia IN( {$transfer_id} )
					AND ( tp.cantidad_cajas_validacion > 0 OR tp.cantidad_paquetes_validacion > 0
					OR tp.cantidad_piezas_validacion > 0 OR tp.total_piezas_validacion > 0 )";
			$stm_2 = $link->query( $sql ) or die( "Error al consultar los detalles que fueron validados en la transferencia por quitar : {$link->error}"  );
			if( $stm_2->num_rows > 0 ){
				while( $row_validated = $stm_2->fetch_assoc() ){
					$sql = "SELECT 
								tp.id_transferencia_producto AS transfer_product_id
							FROM ec_transferencia_productos tp
							LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
							ON tp.id_transferencia = btvd.id_transferencia 
							LEFT JOIN ec_bloques_transferencias_validacion btv
							ON btv.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
							WHERE btvd.id_bloque_transferencia_validacion = {$row_block['block_id']}
							AND tp.id_transferencia NOT IN( {$transfer_id} )
							AND tp.id_proveedor_producto = {$row_validated['product_provider_id']}
							AND tp.id_producto_or = {$row_validated['product_id']}";
				//$resp .= $sql;
					$stm_3 = $link->query( $sql ) or die( "Error al consultar las transferencias que contienen al proveedor-producto : {$link->error}" );
					
					if( $stm_3->num_rows <= 0 ){
					//verifica si el producto existe en alguna transferencia del bloque
						$sql = "SELECT 
									tp.id_transferencia_producto
								FROM ec_transferencia_productos tp
								LEFT JOIN ec_bloques_transferencias_validacion_detalle btvd
								ON tp.id_transferencia = btvd.id_transferencia 
								LEFT JOIN ec_bloques_transferencias_validacion btv
								ON btv.id_bloque_transferencia_validacion = btvd.id_bloque_transferencia_validacion
								WHERE btvd.id_transferencia = {$row_block['block_id']}
								AND tp.id_transferencia NOT IN( {$transfer_id} )
								AND tp.id_producto_or = {$row_validated['product_id']}";
						$stm_4 = $link->query( $sql ) or die( "Error al consultar las transferencias que contienen al producto : {$link->error}" );
						if( $stm_4->num_rows <= 0 ){
							//$resp .= 'here_1 ';
						//crea el registro informativo de asignación
						}else{
							//$resp .= 'here_2 ';
							$row_validated_1 = $stm_4->fetch_assoc();
						}
					}else{
					//crea el registro informativo de asignación
						//elimina los códigos único
					//$row_validated['']
						$transfer_detail_destinity = $stm_3->fetch_assoc();
					//consulta los códigos únicos						
						$sql = "UPDATE ec_transferencias_validacion_usuarios 
									SET id_transferencia_producto = {$transfer_detail_destinity['transfer_product_id']}
								WHERE id_transferencia_producto = {$row_validated['transfer_product_id']}";
						$stm_update = $link->query( $sql ) or die( "Erorr al actualizar los códigos únicos por eliminacion de transferencia del bloque : {$link->error}" );
						//$resp .= $sql;
						$sql = "UPDATE ec_transferencia_productos SET 
										cantidad_cajas_validacion = ( cantidad_cajas_validacion + {$row_validated['validated_boxes']} ),
										cantidad_paquetes_validacion = ( cantidad_paquetes_validacion + {$row_validated['validated_packs']} ),
										cantidad_piezas_validacion = ( cantidad_paquetes_validacion + {$row_validated['validated_pieces']} ),
										total_piezas_validacion = ( total_piezas_validacion + {$row_validated['validated_total']} )
								WHERE id_transferencia_producto = {$transfer_detail_destinity['transfer_product_id']}";
						$stm_update = $link->query( $sql ) or die( "Error al actualizar las piezas validadas en el detalle por eliminacion de transferencia del bloque : {$link->error}" );
						//$resp .= $sql;

						$sql = "UPDATE ec_transferencia_productos SET 
										cantidad_cajas_validacion = 0,
										cantidad_paquetes_validacion = 0,
										cantidad_piezas_validacion = 0,
										total_piezas_validacion = 0
								WHERE id_transferencia_producto = {$row_validated['transfer_product_id']}";
						$stm_update = $link->query( $sql ) or die( "Error al poner en cero las piezas validadas en el detalle por eliminacion de transferencia del bloque : {$link->error}" );
						//$resp .= $sql;
					
					}
				}//fin de while
				$sql = "DELETE FROM ec_bloques_transferencias_validacion_detalle WHERE id_transferencia = {$transfer_id}";
				$stm_5 = $link->query( $sql ) or die( "Error al eliminar la transferncia del bloque de validación : {$link->error}" );
			}
		}
		$link->autocommit( true );
		$resp .= "<p align=\"center\">";
			$resp .= "<button class=\"btn btn-success\" onclick=\"location.reload();\">";
				$resp .= "<i class=\"icon-ok-circle\">Aceptar y recargar pantalla</i>";
			$resp .= "</button>";
		$resp .= "</p>";
		return $resp;
	}
?>
