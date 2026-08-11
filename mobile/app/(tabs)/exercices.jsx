import { useState, useEffect } from 'react';
import {
  View, Text, ScrollView, StyleSheet,
  TouchableOpacity, ActivityIndicator, TextInput, RefreshControl
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { exerciceService } from '../../services/api';
import Colors from '../../constants/colors';

export default function ExercicesScreen() {
  const [exercices, setExercices]   = useState([]);
  const [loading, setLoading]       = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [search, setSearch]         = useState('');

  const fetchExercices = async (nom = '') => {
    try {
      const response = await exerciceService.getAll(nom ? { nom } : {});
      setExercices(response.data);
    } catch (error) {
      console.error('Erreur exercices:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => { fetchExercices(); }, []);

  const onRefresh = () => {
    setRefreshing(true);
    fetchExercices(search);
  };

  const handleSearch = (text) => {
    setSearch(text);
    fetchExercices(text);
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
        <Text style={styles.title}>Exercices</Text>
        <Text style={styles.count}>{exercices.length} exercices</Text>
      </View>

      {/* Barre de recherche */}
      <View style={styles.searchContainer}>
        <Ionicons name="search" size={18} color={Colors.grey} style={styles.searchIcon} />
        <TextInput
          style={styles.searchInput}
          placeholder="Rechercher un exercice..."
          placeholderTextColor={Colors.grey}
          value={search}
          onChangeText={handleSearch}
        />
        {search.length > 0 && (
          <TouchableOpacity onPress={() => handleSearch('')}>
            <Ionicons name="close-circle" size={18} color={Colors.grey} />
          </TouchableOpacity>
        )}
      </View>

      <ScrollView
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={Colors.primary} />}
      >
        <View style={styles.list}>
          {exercices.length === 0 ? (
            <View style={styles.empty}>
              <Ionicons name="library-outline" size={60} color={Colors.grey} />
              <Text style={styles.emptyText}>Aucun exercice trouvé</Text>
            </View>
          ) : (
            exercices.map((exercice) => (
              <View key={exercice.id} style={styles.card}>
                <View style={styles.cardTop}>
                  <View style={styles.cardLeft}>
                    <Text style={styles.cardNom}>{exercice.nom}</Text>
                    {exercice.categorie && (
                      <Text style={styles.cardCategorie}>{exercice.categorie}</Text>
                    )}
                  </View>
                  <View style={[
                    styles.badge,
                    exercice.estPublic ? styles.badgePublic : styles.badgePrivate
                  ]}>
                    <Text style={styles.badgeText}>
                      {exercice.estPublic ? 'Public' : 'Perso'}
                    </Text>
                  </View>
                </View>

                {/* Muscles */}
                {exercice.muscles?.length > 0 && (
                  <View style={styles.muscles}>
                    {exercice.muscles.slice(0, 3).map((muscle) => (
                      <View key={muscle.id} style={styles.muscleTag}>
                        <Text style={styles.muscleText}>{muscle.nom}</Text>
                      </View>
                    ))}
                    {exercice.muscles.length > 3 && (
                      <Text style={styles.muscleMore}>+{exercice.muscles.length - 3}</Text>
                    )}
                  </View>
                )}

                {/* Description */}
                {exercice.description && (
                  <Text style={styles.description} numberOfLines={2}>
                    {exercice.description}
                  </Text>
                )}
              </View>
            ))
          )}
        </View>
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.dark },
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: Colors.dark },
  header: {
    flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center',
    padding: 24, paddingTop: 60,
  },
  title: { fontSize: 28, fontWeight: '800', color: Colors.white },
  count: { fontSize: 13, color: Colors.grey },
  searchContainer: {
    flexDirection: 'row', alignItems: 'center',
    backgroundColor: '#23252C', borderRadius: 10,
    marginHorizontal: 24, marginBottom: 16,
    paddingHorizontal: 12, borderWidth: 1, borderColor: Colors.border,
  },
  searchIcon: { marginRight: 8 },
  searchInput: { flex: 1, padding: 12, color: Colors.white, fontSize: 15 },
  list: { padding: 24, paddingTop: 0, gap: 10 },
  card: {
    backgroundColor: '#23252C', borderRadius: 12,
    padding: 14, borderWidth: 1, borderColor: Colors.border,
  },
  cardTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' },
  cardLeft: { flex: 1, marginRight: 8 },
  cardNom: { fontSize: 15, fontWeight: '700', color: Colors.white },
  cardCategorie: { fontSize: 12, color: Colors.primary, marginTop: 2 },
  badge: { paddingHorizontal: 8, paddingVertical: 3, borderRadius: 12 },
  badgePublic: { backgroundColor: '#1e3a5f' },
  badgePrivate: { backgroundColor: '#3b1f5e' },
  badgeText: { fontSize: 10, fontWeight: '600', color: Colors.white },
  muscles: { flexDirection: 'row', flexWrap: 'wrap', gap: 6, marginTop: 10 },
  muscleTag: {
    backgroundColor: Colors.dark, borderRadius: 6,
    paddingHorizontal: 8, paddingVertical: 3,
    borderWidth: 1, borderColor: Colors.border,
  },
  muscleText: { fontSize: 11, color: Colors.grey },
  muscleMore: { fontSize: 11, color: Colors.grey, alignSelf: 'center' },
  description: { fontSize: 12, color: Colors.grey, marginTop: 8, lineHeight: 18 },
  empty: { alignItems: 'center', padding: 48 },
  emptyText: { fontSize: 16, fontWeight: '600', color: Colors.white, marginTop: 16 },
});