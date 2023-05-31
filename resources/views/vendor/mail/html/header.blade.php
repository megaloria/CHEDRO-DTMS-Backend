@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://oprkm.ched.gov.ph/beta2/wp-content/uploads/2022/02/cropped-ched-logo_pad-1.png" class="logo" alt="CHED Logo">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
