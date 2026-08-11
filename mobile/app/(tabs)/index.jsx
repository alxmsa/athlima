import { useState, useEffect } from 'react';
import {
  View, Text, ScrollView, StyleSheet,
  ActivityIndicator, TouchableOpacity, RefreshControl
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { progressionService } from '../../services/api';
import { useAuth } from '../../context/AuthContext';
import Colors from '../../constants/colors';

export default function DashboardScreen() {
  const { user, logout } = useAuth();
  const [dashboard, setDashboard] = useState(null);
  const [loading, setLoading]     = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const fetchDashboard = async () => {
    try {
      const response = await progressionService.dashboard();
      setDashboard(response.data);
    } catch (error) {
      console.error('Erreur dashboard:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => { fetchDashboard(); }, []);

  const onRefresh = () => {
    setRefreshing(true);
    fetchDashboard();
  };

  if (loading) {
    return (
      <View style={styles.centered}>
        <ActivityIndicator size="large" color={Colors.primary} />
      </View>
    );
  }

  return (
    <ScrollView
      style={styles.container}
      refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={Colors.primary} />}
    >
      {/* Header */}
      <View style={styles.header}>
        <View>
          <Text style={styles.greeting}>Bonjour 👋</Text>
          <Text style={styles.username}>{user?.prenom || 'Athlète'}</Text>
        </View>
        <TouchableOpacity onPress={logout} style={styles.logoutBtn}>
          <Ionicons name="log-out-outline" size={24} color={Colors.grey} />
        </TouchableOpacity>
      </View>

      {/* Stats du mois */}
      <View style={styles.statsRow}>
        <View style={styles.statCard}>
          <Text style={styles.statNumber}>{dashboard?.seancesMoisEnCours ?? 0}</Text>
          <Text style={styles.statLabel}>Séances ce mois</Text>
        </View>
        <View style={styles.statCard}>
          <Text style={styles.statNumber}>{dashboard?.derniersPRs?.length ?? 0}</Text>
          <Text style={styles.statLabel}>Records récents</Text>
        </View>
      </View>

      {/* Derniers PRs */}
      {dashboard?.derniersPRs?.length > 0 && (
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>🏆 Records personnels</Text>
          {dashboard.derniersPRs.map((pr, index) => (
            <View key={index} style={styles.prCard}>
              <View style={styles.prLeft}>
                <Text style={styles.prExercice}>{pr.exercice}</Text>
                <Text style={styles.prDate}>{pr.date}</Text>
              </View>
              <View style={styles.prRight}>
                <Text style={styles.prPoids}>{pr.poidsKg} kg</Text>
                <Text style={styles.prRm}>1RM ~{pr.rm1Estime} kg</Text>
              </View>
            </View>
          ))}
        </View>
      )}

      {/* Dernières séances */}
      {dashboard?.dernieresSeances?.length > 0 && (
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>💪 Dernières séances</Text>
          {dashboard.dernieresSeances.map((seance) => (
            <View key={seance.id} style={styles.seanceCard}>
              <View style={styles.seanceLeft}>
                <Text style={styles.seanceNom}>{seance.nom}</Text>
                <Text style={styles.seanceDate}>{seance.date}</Text>
              </View>
              <View style={[
                styles.badge,
                seance.statut === 'terminee' ? styles.badgeSuccess : styles.badgeWarning
              ]}>
                <Text style={styles.badgeText}>
                  {seance.statut === 'terminee' ? `${seance.dureeMin} min` : 'En cours'}
                </Text>
              </View>
            </View>
          ))}
        </View>
      )}

      {/* Vide */}
      {!dashboard?.dernieresSeances?.length && (
        <View style={styles.empty}>
          <Ionicons name="barbell-outline" size={60} color={Colors.grey} />
          <Text style={styles.emptyText}>Aucune séance pour l'instant</Text>
          <Text style={styles.emptySubtext}>Lance ta première séance !</Text>
        </View>
      )}

    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: Colors.dark },
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: Colors.dark },
  header: {
    flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center',
    padding: 24, paddingTop: 60,
  },
  greeting: { fontSize: 14, color: Colors.grey },
  username: { fontSize: 24, fontWeight: '800', color: Colors.white },
  logoutBtn: { padding: 8 },
  statsRow: { flexDirection: 'row', gap: 12, paddingHorizontal: 24, marginBottom: 24 },
  statCard: {
    flex: 1, backgroundColor: '#23252C', borderRadius: 12,
    padding: 16, alignItems: 'center',
    borderWidth: 1, borderColor: Colors.border,
  },
  statNumber: { fontSize: 32, fontWeight: '800', color: Colors.primary },
  statLabel: { fontSize: 12, color: Colors.grey, marginTop: 4, textAlign: 'center' },
  section: { paddingHorizontal: 24, marginBottom: 24 },
  sectionTitle: { fontSize: 16, fontWeight: '700', color: Colors.white, marginBottom: 12 },
  prCard: {
    flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center',
    backgroundColor: '#23252C', borderRadius: 10, padding: 14, marginBottom: 8,
    borderWidth: 1, borderColor: Colors.border,
  },
  prLeft: { flex: 1 },
  prExercice: { fontSize: 14, fontWeight: '600', color: Colors.white },
  prDate: { fontSize: 12, color: Colors.grey, marginTop: 2 },
  prRight: { alignItems: 'flex-end' },
  prPoids: { fontSize: 16, fontWeight: '700', color: Colors.primary },
  prRm: { fontSize: 11, color: Colors.grey, marginTop: 2 },
  seanceCard: {
    flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center',
    backgroundColor: '#23252C', borderRadius: 10, padding: 14, marginBottom: 8,
    borderWidth: 1, borderColor: Colors.border,
  },
  seanceLeft: { flex: 1 },
  seanceNom: { fontSize: 14, fontWeight: '600', color: Colors.white },
  seanceDate: { fontSize: 12, color: Colors.grey, marginTop: 2 },
  badge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 20 },
  badgeSuccess: { backgroundColor: '#14532d' },
  badgeWarning: { backgroundColor: '#451a03' },
  badgeText: { fontSize: 12, fontWeight: '600', color: Colors.white },
  empty: { alignItems: 'center', padding: 48 },
  emptyText: { fontSize: 16, fontWeight: '600', color: Colors.white, marginTop: 16 },
  emptySubtext: { fontSize: 14, color: Colors.grey, marginTop: 8 },
});