@props(['aircraft', 'qtyAdult' => 0, 'qtyCrew' => 0, 'qtyInfant' => 0, 'expAdult' => 0, 'expCrew' => 0, 'expInfant' => 0])

@if($aircraft->pn_adult || $aircraft->pn_crew || $aircraft->pn_infant)
    <div class="part-number-bar">
        @if($aircraft->pn_adult)
            <div class="pn-item">
                <span class="pn-label" style="color: #60a5fa;">ADULT:</span>
                <span class="pn-value">{{ $aircraft->pn_adult }}</span>
                <span class="pn-qty">{{ $qtyAdult }}</span>
                @if($expAdult > 0)
                    <span class="pn-expired">{{ $expAdult }} expired</span>
                @endif
            </div>
        @endif
        @if($aircraft->pn_crew)
            <div class="pn-item">
                <span class="pn-label" style="color: #fbbf24;">CREW:</span>
                <span class="pn-value">{{ $aircraft->pn_crew }}</span>
                <span class="pn-qty">{{ $qtyCrew }}</span>
                @if($expCrew > 0)
                    <span class="pn-expired">{{ $expCrew }} expired</span>
                @endif
            </div>
        @endif
        @if($aircraft->pn_infant)
            <div class="pn-item">
                <span class="pn-label" style="color: #f472b6;">INFANT:</span>
                <span class="pn-value">{{ $aircraft->pn_infant }}</span>
                <span class="pn-qty">{{ $qtyInfant }}</span>
                @if($expInfant > 0)
                    <span class="pn-expired">{{ $expInfant }} expired</span>
                @endif
            </div>
        @endif
    </div>
@endif