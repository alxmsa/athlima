import { useState, useEffect } from 'react';
import {
  View, Text, ScrollView, StyleSheet,
  TouchableOpacity, ActivityIndicator, Alert, RefreshControl
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { seanceService } from '../../services/api';
import Colors from '../../constants/colors';
import { router } from 'expo-router';

export default function SeancesScreen() {
  const [seances, setSeances]     = useState([]);
  const [loading, setLoading]     = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const fetchSeances = async () => {
    try {
      const response = await seanceService.getAll();
      setSeances(response.data);
    } catch (error) {
      console.error('Erreur séances:', error);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => { fetchSeances(); }, []);

  const onRefresh = () => {
    setRefreshing(true);
    fetchSeances();
  };

  const handleCreateSeance = async () => {
    Alert.prompt(
      'Nouvelle séance',
      'Nom de la séance',
      async (nom) => {
        if (!nom) return;
        try {
          await seanceService.create({ nom });
          fetchSeances();
        } catch (error) {
          Alert.alert('Erreur', 'Une séance est peut-être déjà en cours');
        }
      },
      'plain-text',
      'Push day'
    );
  };

  const handleTerminer = async (id) => {
    Alert.alert(
      'Terminer la séance',
      'Es-tu sûr de vouloir terminer cette séance ?',
      [
        { text: 'Annuler', style: 'cancel' },
        {
          text: 'Terminer',
          onPress: async () => {
            try {
              await seanceService.terminer(id);
              fetchSeances();
            } catch (error) {
              Alert.alert('Erreur', 'Impossible de terminer la séance');
            }
          }
        }
      ]
    );
  };

  const handleDelete = async (id) => {
    Alert.alert(
      'Supprimer',
      'Supprimer cette séance ?',
      [
        { text: 'Annuler', style: 'cancel' },
        {
          text: 'Supprimer',
          style: 'destructive',
          onPress: async () => {
            try {
              await seanceService.delete(id);
              fetchSeances();
            } catch (error) {
              Alert.alert('Erreur', 'Impossible de supprimer');
            }
          }
        }
      ]
    );
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
        <Text style={styles.title}>Mes séances</Text>
        <TouchableOpacity style={styles.addBtn} onPress={handleCreateSeance}>
          <Ionicons name="add" size={24} color={Colors.white} />
        </TouchableOpacity>
      </View>

      <ScrollView
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={Colors.primary} />}
      >
        {seances.length === 0 ? (
          <View style={styles.empty}>
            <Ionicons name="barbell-outline" size={60} color={Colors.grey} />
            <Text style={styles.emptyText}>Aucune séance</Text>
            <Text style={styles.emptySubtext}>Appuie sur + pour créer ta première séance</Text>
          </View>
        ) : (
          <View style={styles.list}>
            {seances.map((seance) => (
                <TouchableOpacity
                    key={seance.id}
                    style={styles.card}
                    onPress={() => router.push(`/seance/${seance.id}`)}
                >
                {/* Infos séance */}
                <View style={styles.cardTop}>
                  <View style={styles.cardLeft}>
                    <Text style={styles.cardNom}>{seance.nom}</Text>
                    <Text style={styles.cardDate}>{seance.dateDebut?.split(' ')[0]}</Text>
                  </View>
                  <View style={[
                    styles.badge,
                    seance.statut === 'terminee' ? styles.badgeSuccess :
                    seance.statut === 'en_cours' ? styles.badgeWarning : styles.badgeError
                  ]}>
                    <Text style={styles.badgeText}>
                      {seance.statut === 'terminee' ? `${seance.dureeMin} min` :
                       seance.statut === 'en_cours' ? '⏱ En cours' : 'Annulée'}
                    </Text>
                  </View>
                </View>

                {/* Actions */}
                <View style={styles.cardActions}>
                  {seance.statut === 'en_cours' && (
                    <TouchableOpacity
                      style={styles.actionBtn}
                      onPress={() => handleTerminer(seance.id)}
                    >
                      <Ionicons name="checkmark-circle" size={18} color={Colors.success} />
                      <Text style={[styles.actionText, { color: Colors.success }]}>Terminer</Text>
                    </TouchableOpacity>
                  )}
                  <TouchableOpacity
                    style={styles.actionBtn}
                    onPress={() => handleDelete(seance.id)}
                  >
                    <Ionicons name="trash-outline" size={18} color={Colors.error} />
                    <Text style={[styles.actionText, { color: Colors.error }]}>Supprimer</Text>
                  </TouchableOpacity>
                </View>
              </TouchableOpacity>
            ))}
          </View>
        )}
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
  addBtn: {
    backgroundColor: Colors.primary, width: 44, height: 44,
    borderRadius: 22, justifyContent: 'center', alignItems: 'center',
  },
  list: { padding: 24, gap: 12 },
  card: {
    backgroundColor: '#23252C', borderRadius: 12,
    padding: 16, borderWidth: 1, borderColor: Colors.border,
  },
  cardTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' },
  cardLeft: { flex: 1 },
  cardNom: { fontSize: 16, fontWeight: '700', color: Colors.white },
  cardDate: { fontSize: 12, color: Colors.grey, marginTop: 4 },
  badge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 20 },
  badgeSuccess: { backgroundColor: '#14532d' },
  badgeWarning: { backgroundColor: '#451a03' },
  badgeError: { backgroundColor: '#450a0a' },
  badgeText: { fontSize: 12, fontWeight: '600', color: Colors.white },
  cardActions: { flexDirection: 'row', gap: 16, marginTop: 12, paddingTop: 12, borderTopWidth: 1, borderTopColor: Colors.border },
  actionBtn: { flexDirection: 'row', alignItems: 'center', gap: 4 },
  actionText: { fontSize: 13, fontWeight: '600' },
  empty: { alignItems: 'center', padding: 48 },
  emptyText: { fontSize: 16, fontWeight: '600', color: Colors.white, marginTop: 16 },
  emptySubtext: { fontSize: 14, color: Colors.grey, marginTop: 8, textAlign: 'center' },
});