<?php

namespace Database\Seeders;

use App\Models\Beach;
use App\Models\BeachTranslation;
use App\Models\BeachService;
use App\Models\BeachFeature;
use App\Models\BeachPredictionProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BeachSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
            ['name' => 'Praia Formosa (Santa Maria)', 'slug' => 'praia-formosa-santa-maria', 'district' => 'Açores', 'municipality' => 'Vila do Porto', 'lat' => 36.9489, 'lon' => -25.0956, 'blue_flag' => true, 'accessible' => true, 'desc' => 'A única praia de areia clara dos Açores, com águas azuis.']
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
