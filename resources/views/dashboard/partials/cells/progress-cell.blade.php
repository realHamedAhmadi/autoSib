<td data-label="{{ $dataLabel }}" class="progress-cell">
    <div class="ui tiny progress">
        <div class="bar" style="width: {{ $percent }}%;"></div>
    </div>
    <small>
        {{ $processed ?? 0 }}
        از
        {{ $total ?? 0 }}
        {{ $unit }}
    </small>
</td>
