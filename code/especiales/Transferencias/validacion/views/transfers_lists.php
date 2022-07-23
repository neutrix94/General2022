<?php
	if( isset( $_POST['$user_id'] ) ){
		include( '../../../../../config.ini.php' );
		include( '../../../../../conectMin.php' );
		include( '../../../../../conexionMysqli.php' );
	}

	function getAssignmentList( $user_id, $link ){
		$resp = '';
		$sql = "SELECT 
					ts.id_transferencia_surtimiento,
					t.folio,
					ts.total_partidas,
					IF( t.id_tipo = 5, 'urgente', 'normal') AS tipo
				FROM ec_transferencias_surtimiento ts
				LEFT JOIN ec_transferencias t ON t.id_transferencia = ts.id_transferencia
				WHERE ts.id_usuario_asignado = '{$user_id}'
				AND ts.id_status_asignacion < 4";
		$stm = $link->query( $sql ) or die( "Error al consultar las Transferencias por surtir : " . $link->error );
		if( $stm->num_rows <= 0 ){
			return '<tr><td colspan="3" align="center">Sin Transferencias asignadas!</td></tr>';
		}
		while ( $r = $stm->fetch_row() ) {
			$resp .= build_list_row( $r );
		}
		return $resp;
	}
	
	function getTransfersListValidation( $link ){
		$sql = "SELECT
					t.id_transferencia AS transfer_id,
					t.folio,
					s1.nombre AS origin,
					s2.nombre AS destination,
					ts.nombre AS status,
					IF( tvd.id_bloque_transferencia_validacion IS NULL, '', tvd.id_bloque_transferencia_validacion ) AS block
				FROM ec_transferencias t
				LEFT JOIN sys_sucursales s1 ON s1.id_sucursal = t.id_sucursal_origen
				LEFT JOIN sys_sucursales s2 ON s2.id_sucursal = t.id_sucursal_destino
				LEFT JOIN ec_estatus_transferencia ts ON ts.id_estatus = t.id_estado
				LEFT JOIN ec_bloques_transferencias_validacion_detalle tvd
				ON tvd.id_transferencia = t.id_transferencia
				LEFT JOIN ec_bloques_transferencias_validacion tv
				ON tv.id_bloque_transferencia_validacion = tvd.id_bloque_transferencia_validacion
				WHERE t.id_estado IN( 3, 4, 5, 6 )
				AND t.id_transferencia > 0";
		$stm = $link->query( $sql ) or die( "Error al consultar las Transferencias por surtir : " . $link->error );
		if( $stm->num_rows <= 0 ){
			return '<tr><td colspan="5" align="center">Sin Transferencias por validar!</td></tr>';
		}
		$counter = 0;
		while ( $r = $stm->fetch_assoc() ) {
			$resp .= build_list_row( $r, $counter );
			$counter ++;
		}
		return $resp;
	}

	function build_list_row( $row, $counter ) {//style=\"background-color : rgba({$row['block']}, 0,0, .5);\"
		$resp = "<tr>
				<td id=\"validation_list_1_{$counter}\" class=\"no_visible\">{$row['transfer_id']}</td>
				<td id=\"validation_list_2_{$counter}\">{$row['folio']}</td>
				<td id=\"validation_list_3_{$counter}\">{$row['origin']}</td>
				<td id=\"validation_list_4_{$counter}\">{$row['destination']}</td>
				<td id=\"validation_list_5_{$counter}\">{$row['status']}</td>
				<td id=\"validation_list_6_{$counter}\">{$row['block']}</td>
				<td id=\"validation_list_7_{$counter}\" align=\"center\">
					<input type=\"checkbox\" id=\"validation_list_8_{$counter}\" onclick=\"getAllGroup( {$counter} );\">
				</td>	
			</tr>";
		return $resp;
	}
?>
<br />
<div class="">
	<div class="list_container">
		<table class="table table-striped table_70">
			<thead class="list_header_sticky">
				<tr>	
					<th>Folio</th>
					<th>Origen</th>
					<th>Destino</th>
					<th>Status</th>
					<th>Bloque</th>
					<th>Revisar</th>
				</tr>	
			</thead>
			<tbody id="transfers_list_content" style="font-size:60%;">
			<?php
				echo getTransfersListValidation( $link );
			?> 
			</tbody>
			<tfoot>
				<tr></tr>
			</tfoot>
		</table>
	</div>
	
	<br />
	
	<div class="row">

		<div class="col-2"></div>

		<div class="col-8">
			<button
				type="button"
				class="btn btn-success form-control"
				onclick="set_transfers();"
			>
				Validar Transferencias
			</button>
		</div>

		<div class="col-2"></div>
	</div>
</div>