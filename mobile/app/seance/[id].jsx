import { useState, useEffect } from 'react';
import {
  View, Text, ScrollView, StyleSheet,
  TouchableOpacity, ActivityIndicator, Alert, TextInput, Modal
} from 'react-native';
import { useLocalSearchParams, router } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import { seanceService, exerciceService, serieService } from '../../services/api';
import Colors from '../../constants/colors';

export default function SeanceDetailScreen() {
  const { id } = useLocalSearchParams();
  const [seance, setSeance]           = useState(null);
  const [exercices, setExercices]     = useState([]);
  const [loading, setLoading]         = useState(true);
  const [modalVisible, setModalVisible] = useState(false);
  const [searchExo, setSearchExo]     = useState('');
  const [searchResults, setSearchResults] = useState([]);
  const [serieModal, setSerieModal]   = useState(null); // ExerciceSeance sélectionné
  const [poids, setPoids]             = useState('');
  const [reps, setReps]               = useState('');

  const fetchSeance = async () => {
    try {
      const [seanceRes, exercicesRes] = await Promise.all([
        seanceService.getOne(id),
        seanceService.getExercices(id),
      ]);
      setSeance(seanceRes.data);
      setExercices(exercicesRes.data);
    } catch (error) {
      console.error('Erreur:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchSeance(); }, [id]);

  // ── Rechercher un exercice ────────────────────────────────────────────────
  const handleSearchExo = async (text) => {
    setSearchExo(text);
    if (text.length < 2) { setSearchResults([]); return; }
    try {
      const response = await exerciceService.getAll({ nom: text });
      setSearchResults(response.data.slice(0, 10));
    } catch (error) {
      console.error('Erreur recherche:', error);
    }
  };

  // ── Ajouter un exercice à la séance ──────────────────────────────────────
  const handleAddExercice = async (exercice) => {
    try {
      await seanceService.addExercice(id, {
        exerciceId: exercice.id,
        nbSeriesCible: 3,
        nbRepsCible: 10,
        tempsReposSec: 90,
      });
      setModalVisible(false);
      setSearchExo('');
      setSearchResults([]);
      fetchSeance();
    } catch (error) {
      Alert.alert('Erreur', 'Impossible d\'ajouter l\'exercice');
    }
  };

  // ── Enregistrer une série ─────────────────────────────────────────────────
  const handleAddSerie = async () => {
    if (!poids || !reps) {
      Alert.alert('Erreur', 'Poids et répétitions obligatoires');
      return;
    }
    try {
      const response = await serieService.create(serieModal.id, {
        poidsKg: parseFloat(poids),
        nbRepsRealisees: parseInt(reps),
      });
      const data = response.data;
      if (data.estPr) {
        Alert.alert('🏆 Nouveau record !', `1RM estimé : ${data.rm1Estime} kg`);
      }
      setPoids('');
      setReps('');
      fetchSeance();
    } catch (error) {
      Alert.alert('Erreur', 'Impossible d\'enregistrer la série');
    }
  };

  // ── Terminer la séance ────────────────────────────────────────────────────
  const handleTerminer = async () => {
    Alert.alert('Terminer la séance', 'Es-tu sûr ?', [
      { text: 'Annuler', style: 'cancel' },
      {
        text: 'Terminer',
        onPress: async () => {
          await seanceService.terminer(id);
          router.back();
        }
      }
    ]);
  };

  if (loading) {
    return (
      <View style={styles.centered}>
        <ActivityIndicator size="large" color={Colors.primary} />
      </View>
    );
  }

  return (
    <View style={styles.container}>

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={() => router.back()}>
          <Ionicons name="arrow-back" size={24} color={Colors.white} />
        </TouchableOpacity>
        <Text style={styles.title}>{seance?.nom}</Text>
        {seance?.statut === 'en_cours' && (
          <TouchableOpacity style={styles.terminerBtn} onPress={handleTerminer}>
            <Text style={styles.terminerText}>Terminer</Text>
          </TouchableOpacity>
        )}
      </View>

      <ScrollView style={styles.scroll}>

        {/* Exercices de la séance */}
        {exercices.map((es) => (
          <View key={es.id} style={styles.exoCard}>
            <View style={styles.exoHeader}>
              <Text style={styles.exoNom}>{es.exercice.nom}</Text>
              <Text style={styles.exoMeta}>{es.nbSeriesCible} × {es.nbRepsCible}</Text>
            </View>

            {/* Séries enregistrées */}
            {es.series?.map((serie) => (
              <View key={serie.id} style={styles.serieRow}>
                <Text style={styles.serieNum}>Série {serie.numSerie}</Text>
                <Text style={styles.serieData}>{serie.poidsKg} kg × {serie.nbRepsRealisees}</Text>
                {serie.estPr && <Text style={styles.prBadge}>🏆 PR</Text>}
              </View>
            ))}

            {/* Bouton ajouter série */}
            {seance?.statut === 'en_cours' && (
              <TouchableOpacity
                style={styles.addSerieBtn}
                onPress={() => setSerieModal(es)}
              >
                <Ionicons name="add" size={16} color={Colors.primary} />
                <Text style={styles.addSerieText}>Ajouter une série</Text>
              </TouchableOpacity>
            )}
          </View>
        ))}

        {/* Bouton ajouter exercice */}
        {seance?.statut === 'en_cours' && (
          <TouchableOpacity
            style={styles.addExoBtn}
            onPress={() => setModalVisible(true)}
          >
            <Ionicons name="add-circle" size={20} color={Colors.primary} />
            <Text style={styles.addExoText}>Ajouter un exercice</Text>
          </TouchableOpacity>
        )}

        {exercices.length === 0 && (
          <View style={styles.empty}>
            <Ionicons name="barbell-outline" size={50} color={Colors.grey} />
            <Text style={styles.emptyText}>Aucun exercice</Text>
            <Text style={styles.emptySubtext}>Ajoute ton premier exercice</Text>
          </View>
        )}

      </ScrollView>

      {/* Modal recherche exercice */}
      <Modal visible={modalVisible} animationType="slide" presentationStyle="pageSheet">
        <View style={styles.modal}>
          <View style={styles.modalHeader}>
            <Text style={styles.modalTitle}>Ajouter un exercice</Text>
            <TouchableOpacity onPress={() => { setModalVisible(false); setSearchExo(''); setSearchResults([]); }}>
              <Ionicons name="close" size={24} color={Colors.white} />
            </TouchableOpacity>
          </View>

          <View style={styles.searchContainer}>
            <Ionicons name="search" size={18} color={Colors.grey} />
            <TextInput
              style={styles.searchInput}
              placeholder="Rechercher..."
              placeholderTextColor={Colors.grey}
              value={searchExo}
              onChangeText={handleSearchExo}
              autoFocus
            />
          </View>

          <ScrollView>
            {searchResults.map((ex) => (
              <TouchableOpacity
                key={ex.id}
                style={styles.searchResult}
                onPress={() => handleAddExercice(ex)}
              >
                <Text style={styles.searchResultNom}>{ex.nom}</Text>
                {ex.categorie && (
                  <Text style={styles.searchResultCat}>{ex.categorie}</Text>
                )}
              </TouchableOpacity>
            ))}
          </ScrollView>
        </View>
      </Modal>

      {/* Modal enregistrer série */}
      <Modal visible={!!serieModal} animationType="slide" presentationStyle="pageSheet">
        <View style={styles.modal}>
          <View style={styles.modalHeader}>
            <Text style={styles.modalTitle}>{serieModal?.exercice?.nom}</Text>
            <TouchableOpacity onPress={() => { setSerieModal(null); setPoids(''); setReps(''); }}>
              <Ionicons name="close" size={24} color={Colors.white} />
            </TouchableOpacity>
          </View>

          <View style={styles.serieForm}>
            <Text style={styles.serieFormLabel}>Poids (kg)</Text>
            <TextInput
              style={styles.serieFormInput}
              placeholder="Ex: 80"
              placeholderTextColor={Colors.grey}
              value={poids}
              onChangeText={setPoids}
              keyboardType="decimal-pad"
            />

            <Text style={styles.serieFormLabel}>Répétitions</Text>
            <TextInput
              style={styles.serieFormInput}
              placeholder="Ex: 8"
              placeholderTextColor={Colors.grey}
              value={reps}
              onChangeText={setReps}
              keyboardType="number-pad"
            />

            <TouchableOpacity style={styles.saveSerieBtn} onPress={handleAddSerie}>
              <Text style={styles.saveSerieText}>Enregistrer la série</Text>
            </TouchableOpacity>

            {/* Séries déjà enregistrées */}
            {serieModal?.series?.length > 0 && (
              <View style={styles.seriesHistory}>
                <Text style={styles.seriesHistoryTitle}>Séries enregistrées</Text>
                {serieModal.series.map((s) => (
                  <View key={s.id} style={styles.serieRow}>
                    <Text style={styles.serieNum}>Série {s.numSerie}</Text>
                    <Text style={styles.serieData}>{s.poidsKg} kg × {s.nbRepsRealisees}</Text>
                    {s.estPr && <Text style={styles.prBadge}>🏆 PR</Text>}
                  </View>
                ))}
              </View>
            )}
          </View>
        </View>
      </Modal>

    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.dark },
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: Colors.dark },
  header: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between',
    padding: 24, paddingTop: 60, gap: 12,
  },
  title: { flex: 1, fontSize: 20, fontWeight: '700', color: Colors.white },
  terminerBtn: {
    backgroundColor: Colors.success, paddingHorizontal: 14,
    paddingVertical: 8, borderRadius: 20,
  },
  terminerText: { color: Colors.white, fontWeight: '700', fontSize: 13 },
  scroll: { flex: 1, padding: 16 },
  exoCard: {
    backgroundColor: '#23252C', borderRadius: 12, padding: 14,
    marginBottom: 12, borderWidth: 1, borderColor: Colors.border,
  },
  exoHeader: { flexDirection: 'row', justifyContent: 'space-between', marginBottom: 10 },
  exoNom: { fontSize: 15, fontWeight: '700', color: Colors.white, flex: 1 },
  exoMeta: { fontSize: 13, color: Colors.grey },
  serieRow: {
    flexDirection: 'row', alignItems: 'center', gap: 8,
    paddingVertical: 6, borderBottomWidth: 1, borderBottomColor: Colors.border,
  },
  serieNum: { fontSize: 12, color: Colors.grey, width: 60 },
  serieData: { fontSize: 14, color: Colors.white, fontWeight: '600', flex: 1 },
  prBadge: { fontSize: 12 },
  addSerieBtn: {
    flexDirection: 'row', alignItems: 'center', gap: 6,
    marginTop: 10, padding: 8, borderRadius: 8,
    borderWidth: 1, borderColor: Colors.primary, borderStyle: 'dashed',
    justifyContent: 'center',
  },
  addSerieText: { fontSize: 13, color: Colors.primary, fontWeight: '600' },
  addExoBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center',
    gap: 8, padding: 16, borderRadius: 12, marginBottom: 24,
    borderWidth: 1, borderColor: Colors.primary, borderStyle: 'dashed',
  },
  addExoText: { fontSize: 15, color: Colors.primary, fontWeight: '600' },
  empty: { alignItems: 'center', padding: 48 },
  emptyText: { fontSize: 16, fontWeight: '600', color: Colors.white, marginTop: 16 },
  emptySubtext: { fontSize: 14, color: Colors.grey, marginTop: 8 },
  modal: { flex: 1, backgroundColor: Colors.dark },
  modalHeader: {
    flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center',
    padding: 24, paddingTop: 40, borderBottomWidth: 1, borderBottomColor: Colors.border,
  },
  modalTitle: { fontSize: 18, fontWeight: '700', color: Colors.white },
  searchContainer: {
    flexDirection: 'row', alignItems: 'center', gap: 8,
    margin: 16, padding: 12, backgroundColor: '#23252C',
    borderRadius: 10, borderWidth: 1, borderColor: Colors.border,
  },
  searchInput: { flex: 1, color: Colors.white, fontSize: 15 },
  searchResult: {
    padding: 16, borderBottomWidth: 1, borderBottomColor: Colors.border,
  },
  searchResultNom: { fontSize: 15, color: Colors.white, fontWeight: '600' },
  searchResultCat: { fontSize: 12, color: Colors.primary, marginTop: 2 },
  serieForm: { padding: 24 },
  serieFormLabel: { fontSize: 13, color: Colors.grey, marginBottom: 6, marginTop: 16 },
  serieFormInput: {
    backgroundColor: '#23252C', borderRadius: 10, padding: 14,
    color: Colors.white, fontSize: 16, borderWidth: 1, borderColor: Colors.border,
  },
  saveSerieBtn: {
    backgroundColor: Colors.primary, borderRadius: 10,
    padding: 16, alignItems: 'center', marginTop: 24,
  },
  saveSerieText: { color: Colors.white, fontSize: 16, fontWeight: '700' },
  seriesHistory: { marginTop: 24 },
  seriesHistoryTitle: { fontSize: 14, fontWeight: '700', color: Colors.white, marginBottom: 10 },
});