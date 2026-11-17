#!/usr/bin/env python3
"""Original cinematic score for the NextVerseSkill showcase. Not copied from any song."""

from __future__ import annotations

import wave
from pathlib import Path

import numpy as np

RATE = 44100
DURATION = 110.0


def midi(n: float) -> float:
    return 440.0 * (2 ** ((n - 69) / 12.0))


def adsr(local: np.ndarray, a: float, d: float, s: float, r: float, hold: float) -> np.ndarray:
    env = np.zeros_like(local)
    env = np.where(local < a, local / max(a, 1e-6), env)
    env = np.where((local >= a) & (local < a + d), 1.0 - (1.0 - s) * ((local - a) / max(d, 1e-6)), env)
    env = np.where((local >= a + d) & (local < hold), s, env)
    env = np.where(local >= hold, s * np.clip(1.0 - (local - hold) / max(r, 1e-6), 0, 1), env)
    return env


def main() -> None:
    n = int(RATE * DURATION)
    t = np.linspace(0.0, DURATION, n, endpoint=False)
    audio = np.zeros(n, dtype=np.float64)

    chords = [
        np.array([midi(48), midi(51), midi(55), midi(60), midi(67)]),  # Cm
        np.array([midi(44), midi(48), midi(51), midi(56), midi(63)]),  # Ab
        np.array([midi(51), midi(55), midi(58), midi(63), midi(70)]),  # Eb
        np.array([midi(46), midi(50), midi(53), midi(58), midi(65)]),  # Bb
    ]
    melody = np.array([
        midi(72), midi(70), midi(67), midi(63),
        midi(70), midi(67), midi(65), midi(62),
        midi(67), midi(63), midi(62), midi(58),
        midi(65), midi(63), midi(60), midi(58),
    ])
    harmony = np.array([
        midi(67), midi(65), midi(63), midi(58),
        midi(65), midi(63), midi(60), midi(58),
        midi(63), midi(58), midi(55), midi(53),
        midi(60), midi(58), midi(55), midi(51),
    ])

    bar = (t // 6.0).astype(int) % 4
    for i, chord in enumerate(chords):
        mask = bar == i
        if not np.any(mask):
            continue
        tt = t[mask]
        pad = np.zeros_like(tt)
        for k, f in enumerate(chord):
            slow = 0.55 + 0.45 * np.sin(2 * np.pi * (0.04 + 0.008 * k) * tt)
            detune = 1.0 + 0.0012 * (k - 2)
            pad += (0.16 - 0.018 * k) * slow * np.sin(2 * np.pi * f * detune * tt)
            pad += 0.05 * np.sin(2 * np.pi * f * 2.0 * tt + 0.35)
        sub = 0.38 * np.sin(2 * np.pi * (chord[0] / 2.0) * tt)
        sub *= 0.55 + 0.45 * np.sin(2 * np.pi * 0.08 * tt)
        fifth = 0.09 * np.sin(2 * np.pi * (chord[2] / 2.0) * tt)
        audio[mask] += pad + sub + fifth

    intro = np.clip(t / 10.0, 0, 1)
    lift = np.clip((t - 22.0) / 14.0, 0, 1)
    resolve = np.clip((t - 78.0) / 12.0, 0, 1)

    note_i = (t // 1.5).astype(int) % len(melody)
    local = t % 1.5
    m_env = adsr(local, 0.035, 0.18, 0.32, 0.85, 0.55)
    freqs = melody[note_i]
    audio += intro * (0.15 + 0.04 * lift) * m_env * np.sin(2 * np.pi * freqs * t)
    audio += intro * 0.045 * m_env * np.sin(2 * np.pi * freqs * 2.01 * t)

    h_env = adsr(local, 0.05, 0.22, 0.22, 0.9, 0.5)
    h_freqs = harmony[note_i]
    audio += lift * 0.09 * h_env * np.sin(2 * np.pi * h_freqs * t + 0.2)

    shimmer = 0.011 * intro * np.sin(2 * np.pi * midi(84) * t) * (0.5 + 0.5 * np.sin(2 * np.pi * 0.11 * t))
    air = 0.010 * np.sin(2 * np.pi * 0.07 * t) * np.sin(2 * np.pi * 2100 * t)
    audio += shimmer + air

    beat = t % 2.0
    pulse = np.where(beat < 0.08, 0.09 * (1 - beat / 0.08) * np.sin(2 * np.pi * 58 * t), 0.0)
    audio += pulse * (0.55 + 0.45 * lift)

    boom_t = t % 12.0
    boom = np.where(boom_t < 0.22, 0.16 * (1 - boom_t / 0.22) * np.sin(2 * np.pi * 42 * t), 0.0)
    audio += boom * intro

    # Soft major-color lift on the last cadence
    audio += resolve * 0.07 * np.sin(2 * np.pi * midi(63) * t)
    audio += resolve * 0.05 * np.sin(2 * np.pi * midi(67) * t)

    fade = np.minimum(1.0, t / 3.4) * np.minimum(1.0, (DURATION - t) / 6.5)
    audio *= fade * 0.40

    delay = int(0.016 * RATE)
    left = audio.copy()
    right = np.zeros_like(audio)
    right[delay:] = audio[:-delay] * 0.93
    right[:delay] = audio[:delay] * 0.78
    stereo = np.stack([left, right], axis=1)
    peak = np.max(np.abs(stereo)) or 1.0
    stereo = np.clip(stereo * (0.89 / peak), -1, 1)
    pcm = (stereo * 32767).astype(np.int16)

    out = Path(__file__).resolve().parent / "score-original.wav"
    with wave.open(str(out), "w") as wav:
        wav.setnchannels(2)
        wav.setsampwidth(2)
        wav.setframerate(RATE)
        wav.writeframes(pcm.tobytes())
    print(f"wrote {out}  ({DURATION:.0f}s)")


if __name__ == "__main__":
    main()
