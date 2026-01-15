<table>
    <thead>
        <tr>
            <th width="40">Producto</th>
            <th width="25">SKU</th>
            <th width="25">Precio - CLiente Final</th>
            <th width="25">Precio - CLiente Empresa</th>
            <th width="25">Categoría</th>
            <th width="25">¿Es regalo?</th>
            <th width="25">¿Descuento?</th>
            <th width="25">Tipo de Impuesto</th>
            <th width="25">Importe Iva</th>
            <th width="25">Disponibilidad</th>
            <th width="20">Estado</th>
            <th width="30">Días de garantía</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($list_products as $product)
            <tr>
                <td>{{ $product->title }}</td>
                <td>{{ $product->sku }}</td>
                <td>{{ $product->price_general }}</td>
                <td>{{ $product->price_company }}</td>
                <td>{{ $product->category->name }}</td>
                <td>{{ $product->is_gift ? 'Si' : 'No' }}</td>
                <td>
                    {{ $product->is_discount ? 'Si' : 'No' }}
                    <br>
                    Descuento: {{ $product->max_discount }} %
                </td>
                <td>{{ $product->is_taxable ? 'Sujeto a Impuest' : 'Libre de Impuesto' }}</td>
                <td>{{ $product->iva }}</td>
                <td> {{ $product->allow_without_stock ? 'Vender sin Stock' : 'No Vender sin Stock' }} </td>
                <td style="background: {{ $product->state ? '#6feb6f' : '#ec4d4d' }}">
                    {{ $product->state ? 'Activo' : 'Inactivo' }}</td>
                <td>{{ $product->warranty_day }} días</td>
            </tr>
        @endforeach
    </tbody>
</table>
