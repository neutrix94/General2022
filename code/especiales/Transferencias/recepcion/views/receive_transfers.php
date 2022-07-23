<?php
?>

	<div class="">
		<div class="group_card">
			<div>
				<label for="barcode_seeker">Escanear código de Barras</label>
			</div>
			<div class="input-group">
				<input
					type="text"
					id="barcode_seeker"
					class="form-control"
					placeholder="Escanear código de Barras"
					onkeyup="validateBarcode( this, event );"
				>
				<button 
					type="button" 
					id="barcode_seeker_lock_btn" 
					class="btn btn-danger"
					onclick="lock_and_unlock_focus( this, '#barcode_seeker' )">
					<i class="icon-lock"></i>
				</button>
				<button 
					type="button" 
					class="btn btn-info"
					onclick="confirm( 'Abrir cámara?' );"	
				>
					<i class="icon-instagram"></i>
				</button>
			</div>
			<div id="seeker_response"></div>
		</div>

		<div class="group_card lasts_products_received">
			<div>
				<label for="">Productos Recibidos (últimos 3)</label>
			</div>
			<table class="table table-striped table_80">
				<thead>
					<tr>
						<th>Producto</th>
						<th>Recibido</th>
						<th>Transferencias</th>
						<th>Ver</th>
					</tr>
				</thead>
				<tbody id="last_received_products">
				<?php
				//	echo getLastReceptions( );
				?>
				</tbody>
				<tfoot></tfoot>
			</table>
		</div>
		
		<br>
		
		<div class="row">
			<div class="col-2"></div>
			<div class="col-8">
				<button
					type="button"
					class="btn btn-success form-control"
				>
					<i class="icon-floppy-1">Guardar</i>
				</button>
			</div>
			<div class="col-2"></div>
		</div>
	</div>