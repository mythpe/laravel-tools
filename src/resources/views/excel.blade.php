<table>
    <thead>
        <tr>
            @foreach($headers as $header)
                <th style="background-color: #D9D9D9;border: 1px solid #111111;">{{ $header }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $cells)
            <tr>
                @foreach($cells as $cell)
                    <td style="border: 1px solid #111111;">{!! nl2br(e($cell?:'')) !!}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
    @if(!empty($footer))
        <tfoot>
            @foreach($footer as $cells)
                <tr>
                    @foreach($cells as $cell)
                        <td style="border: 1px solid #111111;">{!! nl2br(e($cell?:'')) !!}</td>
                    @endforeach
                </tr>
            @endforeach
        </tfoot>
    @endif
</table>
