import { useState, useEffect } from 'react';
import {
  View, Text, ScrollView, StyleSheet,
  TouchableOpacity, ActivityIndicator, TextInput, Alert
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { profilService } from '../../services/api';
import { useAuth } from '../../context/AuthContext';
import Colors from '../../constants/colors';

export default function ProfilScreen() {
  const { logout } = useAuth();
  const [profil, setProfil]     = useState(null);
  const [loading, setLoading]   = useState(true);
  const [editing, setEditing]   = useState(false);
  const [saving, setSaving]     = useState(false);

  // Champs modifiables
  const [prenom, setPrenom]     = useState('');
  const [nom, setNom]           = useState('');
  const [poidsKg, setPoidsKg]   = useState('');
  const [tailleCm, setTailleCm] = useState('');
  const [niveau, setNiveau]     = useState('');

  const fetchProfil = async () => {
    try {
      const response = await profilService.get();
      setProfil(response.data);
      setPrenom(response.data.prenom || '');
      setNom(response.data.nom || '');
      setPoidsKg(response.data.poidsKg?.toString() || '');
      setTailleCm(response.data.tailleCm?.toString() || '');
      setNiveau(response.data.niveau || 'debutant');
    } catch (error) {
      console.error('Erreur profil:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { fetchProfil(); }, []);

  const handleSave = async () => {
    setSaving(true);
    try {
      await profilService.update({
        prenom,
        nom,
        poidsKg: poidsKg ? parseFloat(poidsKg) : null,
        tailleCm: tailleCm ? parseFloat(tailleCm) : null,
        niveau,
      });
      await fetchProfil();
      setEditing(false);
      Alert.alert('Succès', 'Profil mis à jour !');
    } catch (error) {
      Alert.alert('Erreur', 'Impossible de mettre à jour le profil');
    } finally {
      setSaving(false);
    }
  };

  const handleLogout = () => {
    Alert.alert(
      'Déconnexion',
      'Es-tu sûr de vouloir te déconnecter ?',
      [
        { text: 'Annuler', style: 'cancel' },
        { text: 'Déconnexion', style: 'destructive', onPress: logout }
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

  const niveauxLabels = {
    debutant: 'Débutant',
    intermediaire: 'Intermédiaire',
    avance: 'Avancé',
  };

  return (
    <ScrollView style={styles.container}>

      {/* Header */}
      <View style={styles.header}>
        <Text style={styles.title}>Mon profil</Text>
        <TouchableOpacity
          style={styles.editBtn}
          onPress={() => editing ? handleSave() : setEditing(true)}
          disabled={saving}
        >
          {saving
            ? <ActivityIndicator size="small" color={Colors.white} />
            : <Ionicons
                name={editing ? 'checkmark' : 'pencil'}
                size={20}
                color={Colors.white}
              />
          }
        </TouchableOpacity>
      </View>

      {/* Avatar */}
      <View style={styles.avatarSection}>
        <View style={styles.avatar}>
          <Text style={styles.avatarText}>
            {profil?.prenom?.[0]?.toUpperCase() || '?'}
          </Text>
        </View>
        <Text style={styles.fullName}>{profil?.prenom} {profil?.nom}</Text>
        <Text style={styles.email}>{profil?.email}</Text>
        <View style={styles.niveauBadge}>
          <Text style={styles.niveauText}>{niveauxLabels[profil?.niveau] || profil?.niveau}</Text>
        </View>
      </View>

      {/* Infos */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Informations</Text>

        <View style={styles.field}>
          <Text style={styles.fieldLabel}>Prénom</Text>
          {editing
            ? <TextInput style={styles.fieldInput} value={prenom} onChangeText={setPrenom} placeholderTextColor={Colors.grey} />
            : <Text style={styles.fieldValue}>{profil?.prenom || '—'}</Text>
          }
        </View>

        <View style={styles.field}>
          <Text style={styles.fieldLabel}>Nom</Text>
          {editing
            ? <TextInput style={styles.fieldInput} value={nom} onChangeText={setNom} placeholderTextColor={Colors.grey} />
            : <Text style={styles.fieldValue}>{profil?.nom || '—'}</Text>
          }
        </View>

        <View style={styles.field}>
          <Text style={styles.fieldLabel}>Poids (kg)</Text>
          {editing
            ? <TextInput style={styles.fieldInput} value={poidsKg} onChangeText={setPoidsKg} keyboardType="decimal-pad" placeholderTextColor={Colors.grey} />
            : <Text style={styles.fieldValue}>{profil?.poidsKg ? `${profil.poidsKg} kg` : '—'}</Text>
          }
        </View>

        <View style={styles.field}>
          <Text style={styles.fieldLabel}>Taille (cm)</Text>
          {editing
            ? <TextInput style={styles.fieldInput} value={tailleCm} onChangeText={setTailleCm} keyboardType="decimal-pad" placeholderTextColor={Colors.grey} />
            : <Text style={styles.fieldValue}>{profil?.tailleCm ? `${profil.tailleCm} cm` : '—'}</Text>
          }
        </View>

        {/* Niveau */}
        {editing && (
          <View style={styles.field}>
            <Text style={styles.fieldLabel}>Niveau</Text>
            <View style={styles.niveauxRow}>
              {['debutant', 'intermediaire', 'avance'].map((n) => (
                <TouchableOpacity
                  key={n}
                  style={[styles.niveauBtn, niveau === n && styles.niveauBtnActive]}
                  onPress={() => setNiveau(n)}
                >
                  <Text style={[styles.niveauBtnText, niveau === n && styles.niveauBtnTextActive]}>
                    {niveauxLabels[n]}
                  </Text>
                </TouchableOpacity>
              ))}
            </View>
          </View>
        )}
      </View>

      {/* Déconnexion */}
      <TouchableOpacity style={styles.logoutBtn} onPress={handleLogout}>
        <Ionicons name="log-out-outline" size={20} color={Colors.error} />
        <Text style={styles.logoutText}>Se déconnecter</Text>
      </TouchableOpacity>

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
  title: { fontSize: 28, fontWeight: '800', color: Colors.white },
  editBtn: {
    backgroundColor: Colors.primary, width: 40, height: 40,
    borderRadius: 20, justifyContent: 'center', alignItems: 'center',
  },
  avatarSection: { alignItems: 'center', paddingVertical: 24 },
  avatar: {
    width: 80, height: 80, borderRadius: 40,
    backgroundColor: Colors.primary, justifyContent: 'center', alignItems: 'center',
    marginBottom: 12,
  },
  avatarText: { fontSize: 32, fontWeight: '800', color: Colors.white },
  fullName: { fontSize: 22, fontWeight: '700', color: Colors.white },
  email: { fontSize: 14, color: Colors.grey, marginTop: 4 },
  niveauBadge: {
    marginTop: 10, backgroundColor: '#23252C', borderRadius: 20,
    paddingHorizontal: 14, paddingVertical: 6,
    borderWidth: 1, borderColor: Colors.primary,
  },
  niveauText: { fontSize: 13, color: Colors.primary, fontWeight: '600' },
  section: { paddingHorizontal: 24, marginBottom: 24 },
  sectionTitle: { fontSize: 16, fontWeight: '700', color: Colors.white, marginBottom: 12 },
  field: {
    backgroundColor: '#23252C', borderRadius: 10, padding: 14,
    marginBottom: 8, borderWidth: 1, borderColor: Colors.border,
  },
  fieldLabel: { fontSize: 11, color: Colors.grey, marginBottom: 4, textTransform: 'uppercase', letterSpacing: 0.5 },
  fieldValue: { fontSize: 15, color: Colors.white, fontWeight: '500' },
  fieldInput: {
    fontSize: 15, color: Colors.white, fontWeight: '500',
    borderBottomWidth: 1, borderBottomColor: Colors.primary, paddingBottom: 2,
  },
  niveauxRow: { flexDirection: 'row', gap: 8, marginTop: 4 },
  niveauBtn: {
    flex: 1, padding: 8, borderRadius: 8, borderWidth: 1,
    borderColor: Colors.border, alignItems: 'center',
  },
  niveauBtnActive: { backgroundColor: Colors.primary, borderColor: Colors.primary },
  niveauBtnText: { fontSize: 12, color: Colors.grey, fontWeight: '600' },
  niveauBtnTextActive: { color: Colors.white },
  logoutBtn: {
    flexDirection: 'row', alignItems: 'center', justifyContent: 'center',
    gap: 8, margin: 24, padding: 16, borderRadius: 10,
    backgroundColor: '#23252C', borderWidth: 1, borderColor: Colors.error,
  },
  logoutText: { fontSize: 15, fontWeight: '700', color: Colors.error },
});