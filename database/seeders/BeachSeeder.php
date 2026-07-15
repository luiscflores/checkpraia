<?php

namespace Database\Seeders;

use App\Models\Beach;
use App\Models\BeachFeature;
use App\Models\BeachPredictionProfile;
use App\Models\BeachService;
use App\Models\BeachTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BeachSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $beachcamMapping = [
            'praia-de-vila-praia-de-ancora' => 'vila-praia-de-ancora',
            'praia-de-leca-da-palmeira' => 'leca-da-palmeira',
            'praia-de-matosinhos' => 'praia-de-matosinhos',
            'praia-do-furadouro' => 'furadouro',
            'praia-da-barra' => 'praia-da-barra',
            'praia-da-costa-nova' => 'costa-nova',
            'praia-da-vagueira' => 'vagueira',
            'praia-de-mira' => 'praia-de-mira',
            'praia-da-tocha' => 'praia-da-tocha',
            'praia-da-vieira' => 'praia-da-vieira',
            'praia-da-nazare' => 'praia-da-nazare',
            'praia-de-sao-martinho-do-porto' => 'sao-martinho-do-porto',
            'praia-da-foz-do-arelho' => 'foz-do-arelho',
            'praia-do-baleal' => 'peniche-baleal-panoramica',
            'praia-dos-supertubos' => 'peniche-supertubos',
            'praia-do-magoito' => 'praia-do-magoito',
            'praia-grande-sintra' => 'praia-grande',
            'praia-das-macas' => 'praia-das-macas',
            'praia-do-guincho' => 'praia-do-guincho',
            'praia-do-tamariz' => 'tamariz',
            'praia-da-duquesa' => 'praia-da-conceicao-e-duquesa',
            'praia-de-carcavelos' => 'praia-de-carcavelos',
            'praia-de-sao-joao-da-caparica' => 'costa-de-caparica-praia-do-norte',
            'praia-da-comporta' => 'comporta',
            'praia-do-carvalhal' => 'praia-do-carvalhal',
            'praia-do-meco' => 'praia-do-meco',
            'praia-da-zambujeira-do-mar' => 'zambujeira-do-mar',
            'praia-de-odeceixe' => 'odeceixe',
            'praia-da-arrifana' => 'arrifana',
            'praia-do-amado' => 'praia-do-amado',
            'praia-do-alvor' => 'alvor',
            'praia-da-rocha' => 'praia-da-rocha',
            'praia-da-luz' => 'praia-da-luz',
            'praia-de-carvoeiro' => 'carvoeiro',
            'praia-da-gale' => 'praia-da-gale',
            'meia-praia-lagos' => 'meia-praia',
            'praia-da-falesia' => 'praia-da-falesia',
            'praia-de-vilamoura' => 'vilamoura',
            'praia-de-quarteira' => 'praia-quarteira-oeste',
            'praia-da-torreira' => 'praia-da-torreira',
            'praia-do-norte-nazare' => 'praia-do-norte',
            'praia-da-areia-branca' => 'areia-branca',
            'praia-da-adraga' => 'praia-da-adraga',
            'praia-de-esmoriz' => 'esmoriz',
            'praia-da-apúlia' => 'apulia',
            'praia-de-troia-mar' => 'troia',
            'praia-de-sao-pedro-de-moel' => 'sao-pedro-de-moel',
        ];

        $beaches = [
            // --- VIANA DO CASTELO ---
            ['name' => 'Praia de Moledo', 'slug' => 'praia-de-moledo', 'district' => 'Viana do Castelo', 'municipality' => 'Caminha', 'lat' => 41.8491, 'lon' => -8.8744, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia ventosa e selvagem, muito apreciada para windsurf.'],
            ['name' => 'Praia de Vila Praia de Âncora', 'slug' => 'praia-de-vila-praia-de-ancora', 'district' => 'Viana do Castelo', 'municipality' => 'Caminha', 'lat' => 41.8122, 'lon' => -8.8683, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Areal extenso e águas calmas na foz do rio Âncora.'],
            ['name' => 'Praia de Afife', 'slug' => 'praia-de-afife', 'district' => 'Viana do Castelo', 'municipality' => 'Viana do Castelo', 'lat' => 41.7758, 'lon' => -8.8767, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia de forte ondulação e dunas selvagens.'],
            ['name' => 'Praia do Cabedelo', 'slug' => 'praia-do-cabedelo-viana', 'district' => 'Viana do Castelo', 'municipality' => 'Viana do Castelo', 'lat' => 41.6792, 'lon' => -8.8322, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia situada junto à foz do rio Lima, rodeada por pinhal.'],
            ['name' => 'Praia do Castelo do Neiva', 'slug' => 'praia-do-castelo-do-neiva', 'district' => 'Viana do Castelo', 'municipality' => 'Viana do Castelo', 'lat' => 41.6167, 'lon' => -8.8167, 'blue_flag' => true, 'accessible' => false, 'desc' => 'Praia tradicional de pescadores com fortes ventos.'],

            // --- PORTO ---
            ['name' => 'Praia da Redonda', 'slug' => 'praia-da-redonda-povoa', 'district' => 'Porto', 'municipality' => 'Póvoa de Varzim', 'lat' => 41.3789, 'lon' => -8.7694, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia cosmopolita no centro urbano da Póvoa de Varzim.'],
            ['name' => 'Praia dos Fornos', 'slug' => 'praia-dos-fornos-vila-do-conde', 'district' => 'Porto', 'municipality' => 'Vila do Conde', 'lat' => 41.3489, 'lon' => -8.7556, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Excelente areal e águas ricas em iodo.'],
            ['name' => 'Praia de Leça da Palmeira', 'slug' => 'praia-de-leca-da-palmeira', 'district' => 'Porto', 'municipality' => 'Matosinhos', 'lat' => 41.1964, 'lon' => -8.7056, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia extensa abrigada pela piscina das marés de Siza Vieira.'],
            ['name' => 'Praia de Matosinhos', 'slug' => 'praia-de-matosinhos', 'district' => 'Porto', 'municipality' => 'Matosinhos', 'lat' => 41.1764, 'lon' => -8.6917, 'blue_flag' => false, 'accessible' => true, 'desc' => 'Praia urbana muito popular no Porto, ideal para surf.'],
            ['name' => 'Praia de Miramar', 'slug' => 'praia-de-miramar', 'district' => 'Porto', 'municipality' => 'Vila Nova de Gaia', 'lat' => 41.0664, 'lon' => -8.6583, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Famosa pela Capela do Senhor da Pedra edificada no rochedo.'],
            ['name' => 'Praia da Aguda', 'slug' => 'praia-da-aguda', 'district' => 'Porto', 'municipality' => 'Vila Nova de Gaia', 'lat' => 41.0456, 'lon' => -8.6528, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia de pescadores com extensos passadiços de madeira.'],
            ['name' => 'Praia da Granja', 'slug' => 'praia-da-granja', 'district' => 'Porto', 'municipality' => 'Vila Nova de Gaia', 'lat' => 41.0256, 'lon' => -8.6503, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia histórica da aristocracia nortenha do século XIX.'],

            // --- AVEIRO ---
            ['name' => 'Praia da Baía', 'slug' => 'praia-da-baia-espinho', 'district' => 'Aveiro', 'municipality' => 'Espinho', 'lat' => 40.9922, 'lon' => -8.6489, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Coração balnear de Espinho, abrigada por esporão.'],
            ['name' => 'Praia de Cortegaça', 'slug' => 'praia-de-cortegaca', 'district' => 'Aveiro', 'municipality' => 'Ovar', 'lat' => 40.9422, 'lon' => -8.6603, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia com forte ondulação protegida por pinhal bravio.'],
            ['name' => 'Praia do Furadouro', 'slug' => 'praia-do-furadouro', 'district' => 'Aveiro', 'municipality' => 'Ovar', 'lat' => 40.8756, 'lon' => -8.6756, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia muito concorrida ligada à ria e mar.'],
            ['name' => 'Praia da Barra', 'slug' => 'praia-da-barra', 'district' => 'Aveiro', 'municipality' => 'Ílhavo', 'lat' => 40.644167, 'lon' => -8.748333, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia arenosa famosa pelo seu farol imponente.'],
            ['name' => 'Praia da Costa Nova', 'slug' => 'praia-da-costa-nova', 'district' => 'Aveiro', 'municipality' => 'Ílhavo', 'lat' => 40.6139, 'lon' => -8.7514, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Conhecida pelos palheiros coloridos e dunas de areia fina.'],
            ['name' => 'Praia da Vagueira', 'slug' => 'praia-da-vagueira', 'district' => 'Aveiro', 'municipality' => 'Vagos', 'lat' => 40.5639, 'lon' => -8.7656, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia famosa pelos seus tradicionais barcos de xávega.'],
            ['name' => 'Praia de Mira', 'slug' => 'praia-de-mira', 'district' => 'Aveiro', 'municipality' => 'Mira', 'lat' => 40.4536, 'lon' => -8.8028, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Única praia do mundo com Bandeira Azul ininterrupta.'],

            // --- COIMBRA ---
            ['name' => 'Praia da Tocha', 'slug' => 'praia-da-tocha', 'district' => 'Coimbra', 'municipality' => 'Cantanhede', 'lat' => 40.3283, 'lon' => -8.8456, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Extensa praia ladeada por palheiros e dunas nativas.'],
            ['name' => 'Praia de Quiaios', 'slug' => 'praia-de-quiaios', 'district' => 'Coimbra', 'municipality' => 'Figueira da Foz', 'lat' => 40.2189, 'lon' => -8.8983, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia selvagem emoldurada pela Serra da Boa Viagem.'],
            ['name' => 'Praia da Claridade', 'slug' => 'praia-da-claridade', 'district' => 'Coimbra', 'municipality' => 'Figueira da Foz', 'lat' => 40.1506, 'lon' => -8.8686, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia urbana muito ampla e iluminada.'],
            ['name' => 'Praia do Cabedelo', 'slug' => 'praia-do-cabedelo-figueira', 'district' => 'Coimbra', 'municipality' => 'Figueira da Foz', 'lat' => 40.1389, 'lon' => -8.8583, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Excelente praia para a prática de surf e bodyboard.'],

            // --- LEIRIA ---
            ['name' => 'Praia do Pedrógão', 'slug' => 'praia-do-pedrogao', 'district' => 'Leiria', 'municipality' => 'Leiria', 'lat' => 39.9233, 'lon' => -8.9556, 'blue_flag' => true, 'accessible' => true, 'desc' => 'A única praia arenosa pertencente ao município de Leiria.'],
            ['name' => 'Praia da Vieira', 'slug' => 'praia-da-vieira', 'district' => 'Leiria', 'municipality' => 'Marinha Grande', 'lat' => 39.8739, 'lon' => -8.9667, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Famosa pela arte xávega e gastronomia local.'],
            ['name' => 'Praia da Nazaré', 'slug' => 'praia-da-nazare', 'district' => 'Leiria', 'municipality' => 'Nazaré', 'lat' => 39.6014, 'lon' => -9.0722, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Coração da vila da Nazaré, conhecida pelas ondas gigantes.'],
            ['name' => 'Praia de São Martinho do Porto', 'slug' => 'praia-de-sao-martinho-do-porto', 'district' => 'Leiria', 'municipality' => 'Alcobaça', 'lat' => 39.5089, 'lon' => -9.1383, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Baía natural em forma de concha com águas calmas.', 'features' => ['has_bays' => true, 'slope' => 'gentle', 'current_risk' => 'low', 'coast_orientation' => 'SW'], 'profile' => ['shelter_factor' => 2.0, 'exposure_factor' => 0.7, 'current_risk_factor' => 0.3]],
            ['name' => 'Praia da Foz do Arelho', 'slug' => 'praia-da-foz-do-arelho', 'district' => 'Leiria', 'municipality' => 'Caldas da Rainha', 'lat' => 39.4322, 'lon' => -9.2256, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Encontro da lagoa de Óbidos com o oceano Atlântico.'],
            ['name' => 'Praia de Peniche de Cima', 'slug' => 'praia-de-peniche-de-cima', 'district' => 'Leiria', 'municipality' => 'Peniche', 'lat' => 39.3622, 'lon' => -9.3756, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Extenso areal calmo que liga Peniche ao Baleal.'],
            ['name' => 'Praia do Baleal', 'slug' => 'praia-do-baleal', 'district' => 'Leiria', 'municipality' => 'Peniche', 'lat' => 39.3722, 'lon' => -9.3356, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Istmo arenoso com duas frentes marítimas muito populares.'],
            ['name' => 'Praia dos Supertubos', 'slug' => 'praia-dos-supertubos', 'district' => 'Leiria', 'municipality' => 'Peniche', 'lat' => 39.3422, 'lon' => -9.3656, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Famosa pelas ondas rápidas e tubulares (WSL Tour).'],

            // --- LISBOA ---
            ['name' => 'Praia do Magoito', 'slug' => 'praia-do-magoito', 'district' => 'Lisboa', 'municipality' => 'Sintra', 'lat' => 38.8689, 'lon' => -9.4503, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia de duna fóssil com forte teor de iodo.'],
            ['name' => 'Praia Grande', 'slug' => 'praia-grande-sintra', 'district' => 'Lisboa', 'municipality' => 'Sintra', 'lat' => 38.8156, 'lon' => -9.4803, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Meca do bodyboard e surf nacional.'],
            ['name' => 'Praia das Maçãs', 'slug' => 'praia-das-macas', 'district' => 'Lisboa', 'municipality' => 'Sintra', 'lat' => 38.8256, 'lon' => -9.4703, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia familiar servida pelo histórico elétrico de Sintra.'],
            ['name' => 'Praia do Guincho', 'slug' => 'praia-do-guincho', 'district' => 'Lisboa', 'municipality' => 'Cascais', 'lat' => 38.7303, 'lon' => -9.4725, 'blue_flag' => true, 'accessible' => false, 'desc' => 'Praia selvagem em serra de Sintra com vento forte.'],
            ['name' => 'Praia do Tamariz', 'slug' => 'praia-do-tamariz', 'district' => 'Lisboa', 'municipality' => 'Cascais', 'lat' => 38.7019, 'lon' => -9.3992, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia urbana central no Estoril com piscinas oceânicas.'],
            ['name' => 'Praia da Duquesa', 'slug' => 'praia-da-duquesa', 'district' => 'Lisboa', 'municipality' => 'Cascais', 'lat' => 38.6989, 'lon' => -9.4156, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia abrigada no centro de Cascais.'],
            ['name' => 'Praia de Carcavelos', 'slug' => 'praia-de-carcavelos', 'district' => 'Lisboa', 'municipality' => 'Cascais', 'lat' => 38.679444, 'lon' => -9.334722, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Grande e muito concorrida, ideal para surf e voleibol.'],

            // --- SETÚBAL ---
            ['name' => 'Praia de São João da Caparica', 'slug' => 'praia-de-sao-joao-da-caparica', 'district' => 'Setúbal', 'municipality' => 'Almada', 'lat' => 38.6525, 'lon' => -9.2436, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia dunar na extremidade norte da Caparica.'],
            ['name' => 'Praia da Fonte da Telha', 'slug' => 'praia-da-fonte-da-telha', 'district' => 'Setúbal', 'municipality' => 'Almada', 'lat' => 38.5678, 'lon' => -9.1931, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Aldeia piscatória sob a arriba fóssil da Caparica.'],
            ['name' => 'Praia da Figueirinha', 'slug' => 'praia-da-figueirinha', 'district' => 'Setúbal', 'municipality' => 'Setúbal', 'lat' => 38.4839, 'lon' => -8.9406, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Águas calmas em plena serra da Arrábida.'],
            ['name' => 'Praia de Galapos', 'slug' => 'praia-de-galapos', 'district' => 'Setúbal', 'municipality' => 'Setúbal', 'lat' => 38.4844, 'lon' => -8.9656, 'blue_flag' => true, 'accessible' => false, 'desc' => 'Baía paradisíaca protegida de águas transparentes.'],
            ['name' => 'Praia do Portinho da Arrábida', 'slug' => 'praia-do-portinho-da-arrabida', 'district' => 'Setúbal', 'municipality' => 'Setúbal', 'lat' => 38.4792, 'lon' => -8.9833, 'blue_flag' => true, 'accessible' => false, 'desc' => 'Eleita uma das sete maravilhas naturais de Portugal.'],
            ['name' => 'Praia de Troia-Mar', 'slug' => 'praia-de-troia-mar', 'district' => 'Setúbal', 'municipality' => 'Grândola', 'lat' => 38.4908, 'lon' => -8.9056, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Areal calmo com vista sobre a Arrábida.'],
            ['name' => 'Praia da Comporta', 'slug' => 'praia-da-comporta', 'district' => 'Setúbal', 'municipality' => 'Grândola', 'lat' => 38.3806, 'lon' => -8.8022, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia famosa do pinhal selvagem alentejano.'],
            ['name' => 'Praia do Carvalhal', 'slug' => 'praia-do-carvalhal', 'district' => 'Setúbal', 'municipality' => 'Grândola', 'lat' => 38.3039, 'lon' => -8.7889, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Areal ladeado por arrozais e dunas intocadas.'],

            // --- BEJA / ALENTEJO ---
            ['name' => 'Praia do Porto Covo', 'slug' => 'praia-grande-porto-covo', 'district' => 'Setúbal', 'municipality' => 'Sines', 'lat' => 37.8522, 'lon' => -8.7936, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia Grande de Porto Covo, cercada por falésias recortadas.'],
            ['name' => 'Praia do Malhão', 'slug' => 'praia-do-malhao', 'district' => 'Beja', 'municipality' => 'Odemira', 'lat' => 37.7856, 'lon' => -8.7989, 'blue_flag' => true, 'accessible' => false, 'desc' => 'Praia selvagem muito apreciada por naturistas e surfistas.'],
            ['name' => 'Praia de Vila Nova de Milfontes', 'slug' => 'praia-da-franquia-milfontes', 'district' => 'Beja', 'municipality' => 'Odemira', 'lat' => 37.7233, 'lon' => -8.7903, 'blue_flag' => true, 'accessible' => true, 'type' => 'estuarine', 'desc' => 'Praia da Franquia junto à foz do rio Mira.'],
            ['name' => 'Praia do Almograve', 'slug' => 'praia-do-almograve', 'district' => 'Beja', 'municipality' => 'Odemira', 'lat' => 37.6522, 'lon' => -8.7989, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia de rochas escuras e arribas alentejanas.'],
            ['name' => 'Praia da Zambujeira do Mar', 'slug' => 'praia-da-zambujeira-do-mar', 'district' => 'Beja', 'municipality' => 'Odemira', 'lat' => 37.5225, 'lon' => -8.7881, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Baía protegida por arribas altas no sudoeste.'],

            // --- FARO / ALGARVE ---
            ['name' => 'Praia de Odeceixe', 'slug' => 'praia-de-odeceixe', 'district' => 'Faro', 'municipality' => 'Aljezur', 'lat' => 37.4417, 'lon' => -8.7989, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Maravilhosa praia na foz da ribeira de Seixe.'],
            ['name' => 'Praia da Arrifana', 'slug' => 'praia-da-arrifana', 'district' => 'Faro', 'municipality' => 'Aljezur', 'lat' => 37.2922, 'lon' => -8.8656, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Baía abrigada clássica do surf português.'],
            ['name' => 'Praia do Amado', 'slug' => 'praia-do-amado', 'district' => 'Faro', 'municipality' => 'Aljezur', 'lat' => 37.1689, 'lon' => -8.9022, 'blue_flag' => false, 'accessible' => true, 'desc' => 'Referência internacional para escolas de surf.'],
            ['name' => 'Praia do Alvor', 'slug' => 'praia-do-alvor', 'district' => 'Faro', 'municipality' => 'Portimão', 'lat' => 37.1239, 'lon' => -8.5989, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Areal imenso entre a ria de Alvor e o mar.'],
            ['name' => 'Praia da Rocha', 'slug' => 'praia-da-rocha', 'district' => 'Faro', 'municipality' => 'Portimão', 'lat' => 37.117778, 'lon' => -8.535833, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Estância balnear icónica do Algarve.'],
            ['name' => 'Praia da Dona Ana', 'slug' => 'praia-da-dona-ana', 'district' => 'Faro', 'municipality' => 'Lagos', 'lat' => 37.0914, 'lon' => -8.6694, 'blue_flag' => true, 'accessible' => false, 'desc' => 'Famosa pelas formações rochosas imponentes.'],
            ['name' => 'Praia do Camilo', 'slug' => 'praia-do-camilo', 'district' => 'Faro', 'municipality' => 'Lagos', 'lat' => 37.0867, 'lon' => -8.6683, 'blue_flag' => false, 'accessible' => false, 'desc' => 'Pequena calheta algarvia acedida por escadaria.'],
            ['name' => 'Meia Praia', 'slug' => 'meia-praia-lagos', 'district' => 'Faro', 'municipality' => 'Lagos', 'lat' => 37.1139, 'lon' => -8.6556, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Extensa baía arenosa muito calma em Lagos.'],
            ['name' => 'Praia de Armação de Pêra', 'slug' => 'praia-de-armacao-de-pera', 'district' => 'Faro', 'municipality' => 'Silves', 'lat' => 37.1006, 'lon' => -8.3614, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Areal com águas mornas muito popular em Silves.'],
            ['name' => 'Praia de Benagil', 'slug' => 'praia-de-benagil', 'district' => 'Faro', 'municipality' => 'Lagoa', 'lat' => 37.0872, 'lon' => -8.4239, 'blue_flag' => false, 'accessible' => false, 'desc' => 'Perto da famosa gruta de Benagil (algar).'],
            ['name' => 'Praia da Falésia', 'slug' => 'praia-da-falesia', 'district' => 'Faro', 'municipality' => 'Albufeira', 'lat' => 37.0858, 'lon' => -8.1469, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Famosa pelas arribas argilosas vermelhas.'],
            ['name' => 'Praia de Vilamoura', 'slug' => 'praia-de-vilamoura', 'district' => 'Faro', 'municipality' => 'Loulé', 'lat' => 37.0733, 'lon' => -8.1189, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia elegante anexa à marina de Vilamoura.'],
            ['name' => 'Praia de Quarteira', 'slug' => 'praia-de-quarteira', 'district' => 'Faro', 'municipality' => 'Loulé', 'lat' => 37.0689, 'lon' => -8.1022, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Areal urbano protegido por esporões de rocha.'],
            ['name' => 'Praia da Ilha de Tavira', 'slug' => 'praia-da-ilha-de-tavira', 'district' => 'Faro', 'municipality' => 'Tavira', 'lat' => 37.1039, 'lon' => -7.6256, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Areal paradisíaco no Parque Natural da Ria Formosa.'],
            ['name' => 'Praia do Barril', 'slug' => 'praia-do-barril', 'district' => 'Faro', 'municipality' => 'Tavira', 'lat' => 37.0889, 'lon' => -7.6603, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Conhecida pelo cemitério de âncoras na duna.'],
            ['name' => 'Praia da Manta Rota', 'slug' => 'praia-da-manta-rota', 'district' => 'Faro', 'municipality' => 'Vila Real de Santo António', 'lat' => 37.1639, 'lon' => -7.5206, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Águas quentes na transição para o sotavento algarvio.'],
            ['name' => 'Praia de Monte Gordo', 'slug' => 'praia-de-monte-gordo', 'district' => 'Faro', 'municipality' => 'Vila Real de Santo António', 'lat' => 37.1772, 'lon' => -7.4514, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Baía abrigada com a água mais quente de Portugal continental.'],

            // --- PORTUGUESE BEACHES ADDITIONS ---
            ['name' => 'Praia de Salgueiros', 'slug' => 'praia-de-salgueiros', 'district' => 'Porto', 'municipality' => 'Vila Nova de Gaia', 'lat' => 41.1189, 'lon' => -8.6656, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia urbana rochosa de excelente qualidade.'],
            ['name' => 'Praia da Memória', 'slug' => 'praia-da-memoria', 'district' => 'Porto', 'municipality' => 'Matosinhos', 'lat' => 41.2319, 'lon' => -8.7222, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia histórica onde desembarcaram as tropas libertadoras.'],
            ['name' => 'Praia da Torreira', 'slug' => 'praia-da-torreira', 'district' => 'Aveiro', 'municipality' => 'Murtosa', 'lat' => 40.7589, 'lon' => -8.7183, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Areal imenso entre a Ria de Aveiro e o Atlântico.'],
            ['name' => 'Praia do Norte (Nazaré)', 'slug' => 'praia-do-norte-nazare', 'district' => 'Leiria', 'municipality' => 'Nazaré', 'lat' => 39.6106, 'lon' => -9.0833, 'blue_flag' => false, 'accessible' => false, 'desc' => 'Mundialmente famosa pelo Canhão da Nazaré e ondas gigantes.'],
            ['name' => 'Praia de São Pedro de Moel', 'slug' => 'praia-de-sao-pedro-de-moel', 'district' => 'Leiria', 'municipality' => 'Marinha Grande', 'lat' => 39.7589, 'lon' => -9.0303, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia aristocrática ladeada pelo Pinhal de Leiria.'],
            ['name' => 'Praia da Adraga', 'slug' => 'praia-da-adraga', 'district' => 'Lisboa', 'municipality' => 'Sintra', 'lat' => 38.8028, 'lon' => -9.4856, 'blue_flag' => false, 'accessible' => false, 'desc' => 'Praia belíssima encaixada numa profunda arriba.'],
            ['name' => 'Praia da Ribeira d\'Ilhas', 'slug' => 'praia-da-ribeira-dilhas', 'district' => 'Lisboa', 'municipality' => 'Mafra', 'lat' => 38.9878, 'lon' => -9.4189, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Reserva mundial de surf e palco de provas internacionais.'],
            ['name' => 'Praia dos Pescadores (Ericeira)', 'slug' => 'praia-dos-pescadores-ericeira', 'district' => 'Lisboa', 'municipality' => 'Mafra', 'lat' => 38.9639, 'lon' => -9.4183, 'blue_flag' => false, 'accessible' => true, 'desc' => 'Praia central abrigada de barcos tradicionais de pesca.'],
            ['name' => 'Praia da Rainha (Cascais)', 'slug' => 'praia-da-rainha-cascais', 'district' => 'Lisboa', 'municipality' => 'Cascais', 'lat' => 38.6994, 'lon' => -9.4181, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Pequena baía encantadora outrora frequentada pela Rainha D. Amélia.'],
            ['name' => 'Praia de Santo Amaro de Oeiras', 'slug' => 'praia-de-santo-amaro-oeiras', 'district' => 'Lisboa', 'municipality' => 'Oeiras', 'lat' => 38.6856, 'lon' => -9.3083, 'blue_flag' => true, 'accessible' => true, 'type' => 'estuarine', 'desc' => 'Praia estuarina acolhedora no estuário do rio Tejo.'],
            ['name' => 'Praia de Caxias', 'slug' => 'praia-de-caxias', 'district' => 'Lisboa', 'municipality' => 'Oeiras', 'lat' => 38.7011, 'lon' => -9.2736, 'blue_flag' => false, 'accessible' => true, 'type' => 'estuarine', 'desc' => 'Praia de águas calmas perto do Forte de São Bruno.'],
            ['name' => 'Praia do Meco', 'slug' => 'praia-do-meco', 'district' => 'Setúbal', 'municipality' => 'Sesimbra', 'lat' => 38.4878, 'lon' => -9.1856, 'blue_flag' => true, 'accessible' => false, 'desc' => 'Areal extenso conhecido pela argila natural e naturismo.'],
            ['name' => 'Praia da Califórnia', 'slug' => 'praia-da-california', 'district' => 'Setúbal', 'municipality' => 'Sesimbra', 'lat' => 38.4419, 'lon' => -9.1006, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia urbana abrigada de águas azuis na vila de Sesimbra.'],
            ['name' => 'Praia de Galapinhos', 'slug' => 'praia-de-galapinhos', 'district' => 'Setúbal', 'municipality' => 'Setúbal', 'lat' => 38.4844, 'lon' => -8.9622, 'blue_flag' => true, 'accessible' => false, 'desc' => 'Votada a melhor praia da Europa, um recanto tropical na Arrábida.'],
            ['name' => 'Praia do Creiro', 'slug' => 'praia-do-creiro', 'district' => 'Setúbal', 'municipality' => 'Setúbal', 'lat' => 38.4806, 'lon' => -8.9767, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia com ruínas arqueológicas romanas e águas calmas.'],
            ['name' => 'Praia de Melides', 'slug' => 'praia-de-melides', 'district' => 'Setúbal', 'municipality' => 'Grândola', 'lat' => 38.1283, 'lon' => -8.7906, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Famosa barreira arenosa que separa a lagoa de Melides do mar.'],
            ['name' => 'Praia das Furnas (Milfontes)', 'slug' => 'praia-das-furnas-milfontes', 'district' => 'Beja', 'municipality' => 'Odemira', 'lat' => 37.7189, 'lon' => -8.7936, 'blue_flag' => true, 'accessible' => true, 'type' => 'estuarine', 'desc' => 'Praia deslumbrante situada na margem esquerda do rio Mira.'],
            ['name' => 'Praia do Farol (Milfontes)', 'slug' => 'praia-do-farol-milfontes', 'district' => 'Beja', 'municipality' => 'Odemira', 'lat' => 37.7267, 'lon' => -8.7917, 'blue_flag' => true, 'accessible' => true, 'type' => 'estuarine', 'desc' => 'Praia do estuário do rio Mira, com forte corrente na maré vazante.'],
            ['name' => 'Praia da Samoqueira', 'slug' => 'praia-da-samoqueira-sines', 'district' => 'Setúbal', 'municipality' => 'Sines', 'lat' => 37.8689, 'lon' => -8.7914, 'blue_flag' => true, 'accessible' => false, 'desc' => 'Uma sucessão de pequenas praias unidas por grutas e ilhéus.'],
            ['name' => 'Praia de Faro', 'slug' => 'praia-de-faro', 'district' => 'Faro', 'municipality' => 'Faro', 'lat' => 37.0089, 'lon' => -7.9936, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia central da ilha de Faro no Parque da Ria Formosa.'],
            ['name' => 'Praia da Luz', 'slug' => 'praia-da-luz', 'district' => 'Faro', 'municipality' => 'Lagos', 'lat' => 37.0864, 'lon' => -8.7283, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Baía tranquila com a emblemática Rocha Negra.'],
            ['name' => 'Praia de Carvoeiro', 'slug' => 'praia-de-carvoeiro', 'district' => 'Faro', 'municipality' => 'Lagoa', 'lat' => 37.0967, 'lon' => -8.4719, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Pintoresca praia aninhada entre arribas e casas coloridas.'],
            ['name' => 'Praia da Marinha', 'slug' => 'praia-da-marinha', 'district' => 'Faro', 'municipality' => 'Lagoa', 'lat' => 37.0894, 'lon' => -8.4128, 'blue_flag' => true, 'accessible' => false, 'desc' => 'Uma das praias mais bonitas e fotogénicas do mundo.'],
            ['name' => 'Praia da Galé', 'slug' => 'praia-da-gale', 'district' => 'Faro', 'municipality' => 'Albufeira', 'lat' => 37.0828, 'lon' => -8.3189, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Areal extenso com rochas de formas curiosas à beira-mar.'],
            ['name' => 'Praia de São Rafael', 'slug' => 'praia-de-sao-rafael', 'district' => 'Faro', 'municipality' => 'Albufeira', 'lat' => 37.0756, 'lon' => -8.2806, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Calheta de areia fina rodeada por deslumbrantes falésias douradas.'],
            ['name' => 'Praia de Cabanas (Tavira)', 'slug' => 'praia-de-cabanas-tavira', 'district' => 'Faro', 'municipality' => 'Tavira', 'lat' => 37.1306, 'lon' => -7.5922, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Ilha arenosa de águas cálidas em frente a Cabanas.'],
            ['name' => 'Praia de Monte Clérigo', 'slug' => 'praia-de-monte-clerigo', 'district' => 'Faro', 'municipality' => 'Aljezur', 'lat' => 37.3406, 'lon' => -8.8528, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Areal largo que se estende por um vale dunar muito calmo.'],

            // --- MADEIRA ---
            ['name' => 'Praia Formosa', 'slug' => 'praia-formosa-funchal', 'district' => 'Madeira', 'municipality' => 'Funchal', 'lat' => 32.6378, 'lon' => -16.9536, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Maior praia pública do Funchal com calhaus pretos.'],
            ['name' => 'Praia de Machico', 'slug' => 'praia-de-machico', 'district' => 'Madeira', 'municipality' => 'Machico', 'lat' => 32.7189, 'lon' => -16.7656, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Baía com praia artificial de areia dourada importada.'],
            ['name' => 'Complexo Balnear do Porto Moniz', 'slug' => 'piscinas-porto-moniz', 'district' => 'Madeira', 'municipality' => 'Porto Moniz', 'lat' => 32.8689, 'lon' => -17.1689, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Piscinas naturais escavadas na lava vulcânica.'],
            ['name' => 'Praia do Porto Santo', 'slug' => 'praia-porto-santo', 'district' => 'Madeira', 'municipality' => 'Porto Santo', 'lat' => 33.0583, 'lon' => -16.3353, 'blue_flag' => true, 'accessible' => true, 'desc' => '9 km de areia dourada terapêutica contínua.'],

            // --- AÇORES ---
            ['name' => 'Praia de Santa Bárbara', 'slug' => 'praia-de-santa-barbara-azores', 'district' => 'Açores', 'municipality' => 'Ribeira Grande', 'lat' => 37.8189, 'lon' => -25.5344, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia de areia escura nos Açores, mundialmente famosa pelo surf.'],
            ['name' => 'Praia do Fogo (Ribeira Quente)', 'slug' => 'praia-do-fogo-azores', 'district' => 'Açores', 'municipality' => 'Povoação', 'lat' => 37.7389, 'lon' => -25.3056, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Baía onde a água do mar é morna devido a nascentes termais.'],
            ['name' => 'Praia de Porto Pim', 'slug' => 'praia-de-porto-pim', 'district' => 'Açores', 'municipality' => 'Horta', 'lat' => 38.5256, 'lon' => -28.6256, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Baía abrigada histórica na ilha do Faial.'],
            ['name' => 'Praia Formosa (Santa Maria)', 'slug' => 'praia-formosa-santa-maria', 'district' => 'Açores', 'municipality' => 'Vila do Porto', 'lat' => 36.9489, 'lon' => -25.0956, 'blue_flag' => true, 'accessible' => true, 'desc' => 'A única praia de areia clara dos Açores, com águas azuis.'],
            ['name' => 'Praia de Carreço', 'slug' => 'praia-de-carreço', 'district' => 'Viana do Castelo', 'municipality' => 'Viana do Castelo', 'lat' => 41.7411, 'lon' => -8.8742, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia de grande beleza natural, recortada por rochas e dunas.'],
            ['name' => 'Praia da Amorosa', 'slug' => 'praia-da-amorosa', 'district' => 'Viana do Castelo', 'municipality' => 'Viana do Castelo', 'lat' => 41.6425, 'lon' => -8.8256, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Areal extenso muito procurado por famílias e surfistas.'],
            ['name' => 'Praia de Apúlia', 'slug' => 'praia-de-apúlia', 'district' => 'Braga', 'municipality' => 'Esposende', 'lat' => 41.4828, 'lon' => -8.7786, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Famosa pelos moinhos de vento nas dunas e riqueza em iodo.'],
            ['name' => 'Praia de Ofir', 'slug' => 'praia-de-ofir', 'district' => 'Braga', 'municipality' => 'Esposende', 'lat' => 41.5164, 'lon' => -8.7842, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Rodeada por um belo pinhal, com águas calmas e dunas finas.'],
            ['name' => 'Praia da Azurara', 'slug' => 'praia-da-azurara', 'district' => 'Porto', 'municipality' => 'Vila do Conde', 'lat' => 41.3389, 'lon' => -8.7486, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia muito ampla, ideal para a prática de surf e bodyboard.'],
            ['name' => 'Praia de Árvore', 'slug' => 'praia-de-árvore', 'district' => 'Porto', 'municipality' => 'Vila do Conde', 'lat' => 41.3301, 'lon' => -8.7391, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Areal extenso integrado na Reserva Ornamental da Sub-região.'],
            ['name' => 'Praia de Francelos', 'slug' => 'praia-de-francelos', 'district' => 'Porto', 'municipality' => 'Vila Nova de Gaia', 'lat' => 41.0856, 'lon' => -8.6589, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia terapêutica com águas ricas em iodo e passadiços de madeira.'],
            ['name' => 'Praia de Valadares', 'slug' => 'praia-de-valadares', 'district' => 'Porto', 'municipality' => 'Vila Nova de Gaia', 'lat' => 41.1011, 'lon' => -8.6622, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Excelente praia urbana com ótimas infraestruturas de apoio.'],
            ['name' => 'Praia da Madalena', 'slug' => 'praia-da-madalena', 'district' => 'Porto', 'municipality' => 'Vila Nova de Gaia', 'lat' => 41.1078, 'lon' => -8.6639, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia muito popular com parque de campismo próximo.'],
            ['name' => 'Praia de Canide Sul', 'slug' => 'praia-de-canide-sul', 'district' => 'Porto', 'municipality' => 'Vila Nova de Gaia', 'lat' => 41.0964, 'lon' => -8.6611, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia arenosa com bons acessos e passadiços de madeira.'],
            ['name' => 'Praia do Homem do Leme', 'slug' => 'praia-do-homem-do-leme', 'district' => 'Porto', 'municipality' => 'Porto', 'lat' => 41.1603, 'lon' => -8.6872, 'blue_flag' => true, 'accessible' => true, 'desc' => 'A primeira praia do Porto a receber a Bandeira Azul, muito rochosa.'],
            ['name' => 'Praia dos Ingleses', 'slug' => 'praia-dos-ingleses', 'district' => 'Porto', 'municipality' => 'Porto', 'lat' => 41.1517, 'lon' => -8.6792, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia urbana histórica da Foz do Douro, muito frequentada.'],
            ['name' => 'Praia de São Jacinto', 'slug' => 'praia-de-são-jacinto', 'district' => 'Aveiro', 'municipality' => 'Aveiro', 'lat' => 40.6656, 'lon' => -8.7389, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia selvagem integrada na Reserva Natural das Dunas de São Jacinto.'],
            ['name' => 'Praia do Areão', 'slug' => 'praia-do-areão', 'district' => 'Aveiro', 'municipality' => 'Vagos', 'lat' => 40.5222, 'lon' => -8.7856, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia tranquila e deserta com extenso cordão dunar.'],
            ['name' => 'Praia de Esmoriz', 'slug' => 'praia-de-esmoriz', 'district' => 'Aveiro', 'municipality' => 'Ovar', 'lat' => 40.9572, 'lon' => -8.6556, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia muito concorrida, excelente para a prática de desportos náuticos.'],
            ['name' => 'Praia do Torrão do Lameiro', 'slug' => 'praia-do-torrão-do-lameiro', 'district' => 'Aveiro', 'municipality' => 'Ovar', 'lat' => 40.8288, 'lon' => -8.6906, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia isolada em zona de pinhal com mar de forte ondulação.'],
            ['name' => 'Praia da Costa de Lavos', 'slug' => 'praia-da-costa-de-lavos', 'district' => 'Coimbra', 'municipality' => 'Figueira da Foz', 'lat' => 40.0889, 'lon' => -8.8856, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia tranquila na foz do rio Mondego, com pinhal adjacente.'],
            ['name' => 'Praia da Leirosa', 'slug' => 'praia-da-leirosa', 'district' => 'Coimbra', 'municipality' => 'Figueira da Foz', 'lat' => 40.0578, 'lon' => -8.8956, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia de areal muito extenso e forte ondulação atlántica.'],
            ['name' => 'Praia da Murtinheira', 'slug' => 'praia-da-murtinheira', 'district' => 'Coimbra', 'municipality' => 'Figueira da Foz', 'lat' => 40.2306, 'lon' => -8.9056, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia selvagem sob a falésia do Cabo Mondego, muito sossegada.'],
            ['name' => 'Praia da Consolação', 'slug' => 'praia-da-consolação', 'district' => 'Leiria', 'municipality' => 'Peniche', 'lat' => 39.3244, 'lon' => -9.3606, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Famosa pelas propriedades terapêuticas do iodo na rocha.'],
            ['name' => 'Praia da Gambôa', 'slug' => 'praia-da-gambôa', 'district' => 'Leiria', 'municipality' => 'Peniche', 'lat' => 39.3606, 'lon' => -9.3756, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia urbana junto ao Forte de Peniche, com águas tranquilas.'],
            ['name' => 'Praia de São Bernardino', 'slug' => 'praia-de-são-bernardino', 'district' => 'Leiria', 'municipality' => 'Peniche', 'lat' => 39.3106, 'lon' => -9.3456, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Pequena enseada arenosa abrigada pelo vento norte.'],
            ['name' => 'Praia da Areia Branca', 'slug' => 'praia-da-areia-branca', 'district' => 'Lisboa', 'municipality' => 'Lourinhã', 'lat' => 39.2644, 'lon' => -9.3356, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia cosmopolita e animada, muito procurada para surf.'],
            ['name' => 'Praia de Valmitão', 'slug' => 'praia-de-valmitão', 'district' => 'Lisboa', 'municipality' => 'Lourinhã', 'lat' => 39.2144, 'lon' => -9.3456, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Excelente praia para surf com arribas imponentes.'],
            ['name' => 'Praia de Porto Dinheiro', 'slug' => 'praia-de-porto-dinheiro', 'district' => 'Lisboa', 'municipality' => 'Lourinhã', 'lat' => 39.2139, 'lon' => -9.3443, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia de pescadores famosa pelos fósseis de dinossauros nas arribas.'],
            ['name' => 'Praia de Santa Cruz', 'slug' => 'praia-de-santa-cruz', 'district' => 'Lisboa', 'municipality' => 'Torres Vedras', 'lat' => 39.1339, 'lon' => -9.3806, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Famosa pelo Penedo do Guincho e extensos areais dinâmicos.'],
            ['name' => 'Praia de Santa Rita', 'slug' => 'praia-de-santa-rita', 'district' => 'Lisboa', 'municipality' => 'Torres Vedras', 'lat' => 39.1689, 'lon' => -9.3656, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Grande areal cortado por um vale de dunas selvagens.'],
            ['name' => 'Praia de Porto Novo', 'slug' => 'praia-de-porto-novo', 'district' => 'Lisboa', 'municipality' => 'Torres Vedras', 'lat' => 39.1839, 'lon' => -9.3556, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Encaixada na foz do rio Alcabrichel, com mar forte.'],
            ['name' => 'Praia das Paredes da Vitória', 'slug' => 'praia-das-paredes-da-vitória', 'district' => 'Leiria', 'municipality' => 'Alcobaça', 'lat' => 39.7022, 'lon' => -9.0506, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia em vale profundo com belas dunas e ribeira.'],
            ['name' => 'Praia de São Julião', 'slug' => 'praia-de-são-julião', 'district' => 'Lisboa', 'municipality' => 'Sintra', 'lat' => 38.9306, 'lon' => -9.4189, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia selvagem com arribas altas, excelente para surf.'],
            ['name' => 'Praia do Sul', 'slug' => 'praia-do-sul', 'district' => 'Lisboa', 'municipality' => 'Mafra', 'lat' => 38.9589, 'lon' => -9.4156, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia urbana emblemática no centro da Ericeira.'],
            ['name' => 'Praia de São Sebastião', 'slug' => 'praia-de-são-sebastião', 'district' => 'Lisboa', 'municipality' => 'Mafra', 'lat' => 38.9706, 'lon' => -9.4189, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia rochosa com boas ondas no norte da Ericeira.'],
            ['name' => 'Praia de Foz do Lizandro', 'slug' => 'praia-de-foz-do-lizandro', 'district' => 'Lisboa', 'municipality' => 'Mafra', 'lat' => 38.9417, 'lon' => -9.4128, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Extensa praia na foz do rio Lizandro, ideal para famílias e surf.'],
            ['name' => 'Praia da Conceição', 'slug' => 'praia-da-conceição', 'district' => 'Lisboa', 'municipality' => 'Cascais', 'lat' => 38.6994, 'lon' => -9.4156, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia central de Cascais, muito abrigada de águas calmas.'],
            ['name' => 'Praia de Moitas', 'slug' => 'praia-de-moitas', 'district' => 'Lisboa', 'municipality' => 'Cascais', 'lat' => 38.7011, 'lon' => -9.4089, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Pequena praia urbana ligada pelo paredão Cascais-Estoril.'],
            ['name' => 'Praia de São Pedro do Estoril', 'slug' => 'praia-de-são-pedro-do-estoril', 'district' => 'Lisboa', 'municipality' => 'Cascais', 'lat' => 38.6939, 'lon' => -9.3689, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Muito procurada para a prática de surf e bodyboard em arribas.'],
            ['name' => 'Praia da Parede', 'slug' => 'praia-da-parede', 'district' => 'Lisboa', 'municipality' => 'Cascais', 'lat' => 38.6856, 'lon' => -9.3556, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Famosa pela riqueza em iodo recomendada para ossos.'],
            ['name' => 'Praia da Torre', 'slug' => 'praia-da-torre', 'district' => 'Lisboa', 'municipality' => 'Oeiras', 'lat' => 38.6778, 'lon' => -9.3206, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia abrigada junto ao Forte de São Julião da Barra.'],
            ['name' => 'Praia de Paço de Arcos', 'slug' => 'praia-de-paço-de-arcos', 'district' => 'Lisboa', 'municipality' => 'Oeiras', 'lat' => 38.6944, 'lon' => -9.2939, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Pequena praia de pescadores restaurada na Linha de Cascais.'],
            ['name' => 'Praia do CDS', 'slug' => 'praia-do-cds', 'district' => 'Setúbal', 'municipality' => 'Almada', 'lat' => 38.6439, 'lon' => -9.2389, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia central da Costa da Caparica muito animada.'],
            ['name' => 'Praia da Riviera', 'slug' => 'praia-da-riviera', 'district' => 'Setúbal', 'municipality' => 'Almada', 'lat' => 38.6306, 'lon' => -9.2306, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Areal com boas infraestruturas na Costa da Caparica.'],
            ['name' => 'Praia do Rei', 'slug' => 'praia-do-rei', 'district' => 'Setúbal', 'municipality' => 'Almada', 'lat' => 38.6089, 'lon' => -9.2156, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia muito espaçosa servida pelo minicomboio da Caparica.'],
            ['name' => 'Praia da Cabana do Pescador', 'slug' => 'praia-da-cabana-do-pescador', 'district' => 'Setúbal', 'municipality' => 'Almada', 'lat' => 38.5989, 'lon' => -9.2089, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia familiar de dunas suaves e bons apoios de praia.'],
            ['name' => 'Praia da Nova Vaga', 'slug' => 'praia-da-nova-vaga', 'district' => 'Setúbal', 'municipality' => 'Almada', 'lat' => 38.5839, 'lon' => -9.2006, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Meca de kitesurf nacional na Costa de Caparica.'],
            ['name' => 'Praia do Tarquínio-Paraíso', 'slug' => 'praia-do-tarquínio-paraíso', 'district' => 'Setúbal', 'municipality' => 'Almada', 'lat' => 38.6417, 'lon' => -9.2369, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia urbana central com paredão pedonal e cafés.'],
            ['name' => 'Praia de Alfarim', 'slug' => 'praia-de-alfarim', 'district' => 'Setúbal', 'municipality' => 'Sesimbra', 'lat' => 38.4878, 'lon' => -9.1856, 'blue_flag' => true, 'accessible' => false, 'desc' => 'Areal sem fim continuação da praia do Meco.'],
            ['name' => 'Praia da Lagoa de Albufeira', 'slug' => 'praia-da-lagoa-de-albufeira', 'district' => 'Setúbal', 'municipality' => 'Sesimbra', 'lat' => 38.5089, 'lon' => -9.1806, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Lagoa calma que se encontra com o mar aberto.'],
            ['name' => 'Praia de São Torpes', 'slug' => 'praia-de-são-torpes', 'district' => 'Setúbal', 'municipality' => 'Sines', 'lat' => 37.9222, 'lon' => -8.8056, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Famosa pelas águas aquecidas pela central termoelétrica.'],
            ['name' => 'Praia de Morgavel', 'slug' => 'praia-de-morgavel', 'district' => 'Setúbal', 'municipality' => 'Sines', 'lat' => 37.9056, 'lon' => -8.7956, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia selvagem arenosa com fáceis acessos rodoviários.'],
            ['name' => 'Praia da Vieirinha', 'slug' => 'praia-da-vieirinha', 'district' => 'Setúbal', 'municipality' => 'Sines', 'lat' => 37.8939, 'lon' => -8.7917, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Excelente para a pesca desportiva e amantes da tranquilidade.'],
            ['name' => 'Praia da Ilha do Pessegueiro', 'slug' => 'praia-da-ilha-do-pessegueiro', 'district' => 'Setúbal', 'municipality' => 'Sines', 'lat' => 37.8306, 'lon' => -8.7906, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Com vista para a ilha imortalizada por Rui Veloso.'],
            ['name' => 'Praia de Carvalhal (Odemira)', 'slug' => 'praia-de-carvalhal-odemira', 'district' => 'Beja', 'municipality' => 'Odemira', 'lat' => 37.5689, 'lon' => -8.7889, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Bela enseada alentejana na foz do vale com bons apoios.'],
            ['name' => 'Praia da Bordeira', 'slug' => 'praia-da-bordeira', 'district' => 'Faro', 'municipality' => 'Aljezur', 'lat' => 37.1956, 'lon' => -8.9006, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Dunas imensas que se fundem com a foz da ribeira.'],
            ['name' => 'Praia de Vale Figueiras', 'slug' => 'praia-de-vale-figueiras', 'district' => 'Faro', 'municipality' => 'Aljezur', 'lat' => 37.2489, 'lon' => -8.8806, 'blue_flag' => true, 'accessible' => false, 'desc' => 'Praia selvagem e isolada com forte ondulação atlântica.'],
            ['name' => 'Praia da Oura', 'slug' => 'praia-da-oura', 'district' => 'Faro', 'municipality' => 'Albufeira', 'lat' => 37.0856, 'lon' => -8.2256, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Cosmopolita e muito animada perto da zona de bares.'],
            ['name' => 'Praia dos Pescadores', 'slug' => 'praia-dos-pescadores', 'district' => 'Faro', 'municipality' => 'Albufeira', 'lat' => 37.0872, 'lon' => -8.2522, 'blue_flag' => true, 'accessible' => true, 'desc' => 'A praia mais famosa do centro histórico de Albufeira.'],
            ['name' => 'Praia de Santa Eulália', 'slug' => 'praia-de-santa-eulália', 'district' => 'Faro', 'municipality' => 'Albufeira', 'lat' => 37.0856, 'lon' => -8.2111, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia sofisticada com excelente enquadramento paisagístico.'],
            ['name' => 'Praia Maria Luísa', 'slug' => 'praia-maria-luísa', 'district' => 'Faro', 'municipality' => 'Albufeira', 'lat' => 37.0872, 'lon' => -8.2006, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Bela enseada sob falésias de tons quentes e pinheiros.'],
            ['name' => 'Praia dos Olhos de Água', 'slug' => 'praia-dos-olhos-de-água', 'district' => 'Faro', 'municipality' => 'Albufeira', 'lat' => 37.0889, 'lon' => -8.1889, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Conhecida pelas nascentes de água doce na areia (olheiros).'],
            ['name' => 'Praia de Vale do Lobo', 'slug' => 'praia-de-vale-do-lobo', 'district' => 'Faro', 'municipality' => 'Loulé', 'lat' => 37.0536, 'lon' => -8.0603, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia exclusiva integrada em luxuoso empreendimento turístico.'],
            ['name' => 'Praia da Quinta do Lago', 'slug' => 'praia-da-quinta-do-lago', 'district' => 'Faro', 'municipality' => 'Loulé', 'lat' => 37.0289, 'lon' => -8.0189, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Acedida por ponte pedonal de madeira sobre a ria Formosa.'],
            ['name' => 'Praia de Cacela Velha', 'slug' => 'praia-de-cacela-velha', 'district' => 'Faro', 'municipality' => 'Tavira', 'lat' => 37.1539, 'lon' => -7.5389, 'blue_flag' => false, 'accessible' => false, 'desc' => 'Península arenosa virgem com vista para a bela vila histórica.'],
            ['name' => 'Praia da Fuzeta', 'slug' => 'praia-da-fuzeta', 'district' => 'Faro', 'municipality' => 'Olhão', 'lat' => 37.0422, 'lon' => -7.7406, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Areal calmo na ilha da Armona frente a Fuzeta.'],
            ['name' => 'Praia da Armona', 'slug' => 'praia-da-armona', 'district' => 'Faro', 'municipality' => 'Olhão', 'lat' => 37.0256, 'lon' => -7.7706, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Extensa praia arenosa de águas mornas e transparentes.'],
            ['name' => 'Praia de Burgau', 'slug' => 'praia-de-burgau', 'district' => 'Faro', 'municipality' => 'Vila do Bispo', 'lat' => 37.0717, 'lon' => -8.7756, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Pequena praia abrigada na simpática aldeia piscatória.'],
            ['name' => 'Praia da Salema', 'slug' => 'praia-da-salema', 'district' => 'Faro', 'municipality' => 'Vila do Bispo', 'lat' => 37.0656, 'lon' => -8.8222, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Com pegadas de dinossauro nas rochas e barcos tradicionais.'],
            ['name' => 'Praia de Porto de Mós', 'slug' => 'praia-de-porto-de-mós', 'district' => 'Faro', 'municipality' => 'Lagos', 'lat' => 37.0856, 'lon' => -8.6889, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Grande areal encaixado entre arribas de argila azul.'],
            ['name' => 'Praia da Mareta', 'slug' => 'praia-da-mareta', 'district' => 'Faro', 'municipality' => 'Vila do Bispo', 'lat' => 36.9964, 'lon' => -8.9389, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Primeira praia da costa sul, muito perto de Sagres.'],
            ['name' => 'Praia do Martinhal', 'slug' => 'praia-do-martinhal', 'district' => 'Faro', 'municipality' => 'Vila do Bispo', 'lat' => 37.0189, 'lon' => -8.9289, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Baía protegida ideal para windsurf e vela.'],
            ['name' => 'Praia do Castelejo', 'slug' => 'praia-do-castelejo', 'district' => 'Faro', 'municipality' => 'Vila do Bispo', 'lat' => 37.0989, 'lon' => -8.9489, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Praia selvagem em plena Costa Vicentina de rochas negras.'],
            ['name' => 'Praia do Cordoama', 'slug' => 'praia-do-cordoama', 'district' => 'Faro', 'municipality' => 'Vila do Bispo', 'lat' => 37.1139, 'lon' => -8.9489, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Areal gigante ladeado por altas falésias escuras.'],
            ['name' => 'Praia dos Salgados', 'slug' => 'praia-dos-salgados', 'district' => 'Faro', 'municipality' => 'Albufeira', 'lat' => 37.0856, 'lon' => -8.3306, 'blue_flag' => true, 'accessible' => true, 'desc' => 'Integrada em reserva dunar e lagoa com aves migratórias.'],
        ];

        foreach ($beaches as $index => $data) {
            $region = 'Continental';
            if ($data['district'] === 'Madeira') {
                $region = 'Madeira';
            } elseif ($data['district'] === 'Açores') {
                $region = 'Açores';
            }

            // Create beach
            $beach = Beach::create([
                'external_id' => (string) (2000 + $index),
                'name' => $data['name'],
                'slug' => $data['slug'],
                'beachcam_slug' => $beachcamMapping[$data['slug']] ?? null,
                'type' => $data['type'] ?? 'oceanic',
                'region' => $region,
                'district' => $data['district'],
                'municipality' => $data['municipality'],
                'latitude' => $data['lat'],
                'longitude' => $data['lon'],
                'season_start' => now()->startOfYear(),
                'season_end' => now()->endOfYear(),
                'lifeguard_start' => '09:00:00',
                'lifeguard_end' => '19:00:00',
                'blue_flag' => $data['blue_flag'],
                'accessible' => $data['accessible'],
                'tide_station_id' => Str::slug($data['municipality']),
                'weather_zone' => Str::slug($data['district']),
                'ocean_zone' => $region === 'Continental' ? 'centro' : 'ilhas',
            ]);

            // Translations
            BeachTranslation::create([
                'beach_id' => $beach->id,
                'locale' => 'pt',
                'name' => $data['name'],
                'description' => $data['desc'],
            ]);
            BeachTranslation::create([
                'beach_id' => $beach->id,
                'locale' => 'en',
                'name' => $data['name'],
                'description' => 'Beautiful supervised coastal beach in Portugal.',
            ]);
            BeachTranslation::create([
                'beach_id' => $beach->id,
                'locale' => 'es',
                'name' => $data['name'],
                'description' => 'Playa vigilada en la costa portuguesa.',
            ]);
            BeachTranslation::create([
                'beach_id' => $beach->id,
                'locale' => 'fr',
                'name' => $data['name'],
                'description' => 'Plage surveillée de la côte portugaise.',
            ]);

            // Services
            BeachService::create([
                'beach_id' => $beach->id,
                'parking' => true,
                'bathrooms' => true,
                'showers' => true,
                'accessible' => $data['accessible'],
                'amphibious_chair' => $data['accessible'],
                'first_aid' => true,
                'lifeguard_post' => true,
                'bar' => true,
                'restaurant' => true,
                'surf_school' => rand(0, 1) === 1,
                'equipment_rental' => rand(0, 1) === 1,
            ]);

            // Features
            BeachFeature::create(array_merge([
                'beach_id' => $beach->id,
                'coast_orientation' => 'W',
                'exposure_direction' => 'W',
                'exposure_factor' => 1.0,
                'shelter_factor' => 1.0,
                'beach_type' => 'sandy',
                'bottom_type' => 'sand',
                'slope' => 'medium',
                'current_risk' => 'medium',
                'has_jetties' => false,
                'has_bays' => false,
                'has_cliffs' => false,
                'has_rocks' => false,
                'river_influence' => false,
            ], $data['features'] ?? []));

            // Prediction Profile
            BeachPredictionProfile::create(array_merge([
                'beach_id' => $beach->id,
                'exposure_factor' => 1.00,
                'shelter_factor' => 1.00,
                'current_risk_factor' => 1.00,
                'wave_height_weight' => 1.00,
                'wind_weight' => 1.00,
            ], $data['profile'] ?? []));
        }
    }
}
