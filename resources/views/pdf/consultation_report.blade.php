<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #1e293b; margin: 20px; font-size: 14px; }
        .header { text-align: center; border-bottom: 5px solid #00A651; padding-bottom: 15px; margin-bottom: 25px; }
        .title { color: #00A651; text-transform: uppercase; font-size: 24px; font-weight: bold; margin: 0; }
        
        .section-info { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .label { font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: bold; display: block; margin-bottom: 2px; }
        .value { font-size: 15px; font-weight: bold; color: #0f172a; }

        /* Style des blocs de données */
        .card { margin-bottom: 20px; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; }
        .card-header { background: #f8fafc; padding: 10px 15px; border-bottom: 1px solid #e2e8f0; color: #00A651; font-weight: bold; text-transform: uppercase; font-size: 12px; }
        .card-body { padding: 15px; }
        
        .grid { width: 100%; border-collapse: collapse; }
        .grid td { padding: 10px; border: 1px solid #f1f5f9; width: 50%; }

        .highlight-blue { border-left: 5px solid #3b82f6; background: #eff6ff; }
        .highlight-rose { border-left: 5px solid #f43f5e; background: #fff1f2; }
        .highlight-emerald { border-left: 5px solid #10b981; background: #ecfdf5; }

        .footer { margin-top: 40px; text-align: right; font-size: 11px; color: #64748b; }
        .exam-image { text-align: center; margin-top: 20px; }
        .exam-image img { max-width: 100%; max-height: 380px; border-radius: 10px; border: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="title">Hôpital:</h1>
        <p style="margin:5px 0; font-weight:bold; color:#64748b;">RAPPORT DE CONSULTATION PRÉNATALE N°{{ $c->cpn_number }}</p>
    </div>

    <table class="section-info">
        <tr>
            <td><span class="label">Patiente</span><span class="value">{{ $c->patient_nom }} {{ $c->patient_prenom }}</span></td>
            <td align="right"><span class="label">Date de l'examen</span><span class="value">{{ $date }}</span></td>
        </tr>
        <tr>
            <td><span class="label">Praticien</span><span class="value">Dr. {{ $c->doctor_nom }} {{ $c->doctor_prenom }}</span></td>
            <td align="right"><span class="label">Identifiant Acte</span><span class="value">{{ substr($c->id, 0, 8) }}...</span></td>
        </tr>
    </table>

    <!-- 1. BLOC COMMUN : CONSTANTES (Visible pour toutes les CPN) -->
    <div class="card">
        <div class="card-header">Paramètres Cliniques</div>
        <div class="card-body">
            <table class="grid">
                <tr>
                    <td><span class="label">Poids</span><span class="value">{{ $c->poids }} kg</span></td>
                    <td><span class="label">Tension Artérielle</span><span class="value">{{ $c->tension_arterielle }}</span></td>
                </tr>
                <tr>
                    <td><span class="label">Hauteur Utérine</span><span class="value">{{ $c->hauteur_uterine }} cm</span></td>
                    <td><span class="label">Bruit Cœur Fœtal</span><span class="value">{{ $c->bcf }}</span></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- 2. BLOC DYNAMIQUE : CPN 1 (Bilan Initial) -->
    @if($c->cpn_number == 1)
    <div class="card highlight-blue">
        <div class="card-header" style="color:#1d4ed8">Bilan Obstétrical Initial</div>
        <div class="card-body">
            <table class="grid">
                <tr>
                    <td><span class="label">Groupe Sanguin / Rh</span><span class="value">{{ $c->gs_rh ?? '--' }}</span></td>
                    <td><span class="label">Électrophorèse Hb</span><span class="value">{{ $c->electrophorese_hb ?? '--' }}</span></td>
                </tr>
                <tr>
                    <td><span class="label">Gestité (G)</span><span class="value">{{ $c->gestite_g ?? '0' }}</span></td>
                    <td><span class="label">Parité (P)</span><span class="value">{{ $c->parite_p ?? '0' }}</span></td>
                </tr>
            </table>
        </div>
    </div>
    @endif

    <!-- 3. BLOC DYNAMIQUE : CPN 6, 7, 8 (Examen du Terme) -->
    @if($c->cpn_number >= 6)
    <div class="card highlight-rose">
        <div class="card-header" style="color:#be123c">Évaluation de fin de grossesse</div>
        <div class="card-body">
            <table class="grid">
                <tr>
                    <td><span class="label">Présentation Fœtale</span><span class="value">{{ $c->presentation_foetus ?? 'Non spécifiée' }}</span></td>
                    <td><span class="label">Évaluation du Bassin</span><span class="value">{{ $c->bassin ?? 'Non évalué' }}</span></td>
                </tr>
                <tr>
                    <td><span class="label">Position du Col</span><span class="value">{{ $c->col_position ?? '--' }}</span></td>
                    <td><span class="label">Ouverture du Col</span><span class="value">{{ $c->col_ouverture ?? '--' }}</span></td>
                </tr>
            </table>
        </div>
    </div>
    @endif

    <!-- 4. BLOC DYNAMIQUE : CPN 8 (Pronostic Final) -->
    @if($c->cpn_number == 8)
    <div class="card highlight-emerald">
        <div class="card-header" style="color:#047857">Décision et Pronostic d'Accouchement</div>
        <div class="card-body">
            <p><span class="label">Voie d'accouchement prévue</span><span class="value" style="font-size:20px">{{ $c->pronostic ?? 'VOIE BASSE' }}</span></p>
        </div>
    </div>
    @endif

 <!-- OBSERVATIONS -->
    <div style="margin-top: 20px;">
        <p style="color: #00A651; font-weight: bold; font-size: 12px; margin-bottom: 5px;">OBSERVATIONS ET CONCLUSIONS :</p>
        <div style="border: 1px solid #e2e8f0; padding: 10px; min-height: 50px; font-style: italic;">
            {{ $c->observations ?? 'Rien à signaler' }}
        </div>
    </div>

    <!-- IMAGE DE L'EXAMEN (Si elle existe) -->
    @if($image)
    <div style="text-align: center; margin-top: 30px;">
        <p style="font-size: 10px; color: #64748b; text-transform: uppercase;">Document d'examen joint</p>
        <img src="{{ $image }}" style="max-width: 100%; max-height: 300px; border: 1px solid #e2e8f0; border-radius: 8px;">
    </div>
    @endif

    <!-- PIED DE PAGE ET SIGNATURE -->
    <div style="margin-top: 50px; width: 100%;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 60%; border: none; font-size: 10px; color: #64748b; vertical-align: bottom;">
                    Ce document est un relevé officiel généré par E-CPN.
                </td>
                <td style="width: 40%; border: none; text-align: center;">
                    <p style="font-weight: bold; font-size: 12px; margin-bottom: 10px;">Signature du Praticien</p>
                    @if($signature)
                        <img src="{{ $signature }}" style="width: 150px;">
                    @else
                        <div style="margin-top: 40px; border-bottom: 1px solid #000; width: 100px; margin-left: auto; margin-right: auto;"></div>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</body>
</html>