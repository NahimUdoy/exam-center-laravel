#!/usr/bin/env python3
"""Render a 1080p cinematic feature-tour of NextVerseSkill. Original score, no licensed music."""

from __future__ import annotations

import subprocess
import sys
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont

ROOT = Path(__file__).resolve().parents[2]
SHOW = Path(__file__).resolve().parent
STILLS = SHOW / "stills"
SHOTS = ROOT / "docs" / "screenshots"
BUILD = SHOW / "build"
FONT = "/System/Library/Fonts/Supplemental/Georgia.ttf"
FONT_BOLD = "/System/Library/Fonts/Supplemental/Georgia Bold.ttf"
FPS = 30
XFADE = 0.48

# (image, seconds, ken-burns, lower-third title, caption)
SCENES = [
    (STILLS / "nvs-open-travel.jpg", 5.2, "in", "THE JOURNEY", "A student path through every exam"),
    (STILLS / "nvs-open-title.jpg", 6.2, "in", "NEXTVERSESKILL", "Practice today. Achieve tomorrow."),
    (SHOTS / "01-home.jpg", 5.8, "in", "01  HOME", "IELTS, PTE, MBA, BBA — the academy front door"),
    (SHOTS / "02-level-check.jpg", 4.8, "right", "LEVEL CHECK", "One minute. A level. A starting line."),
    (SHOTS / "03-academy.jpg", 5.0, "in", "02  ACADEMY", "A personal plan after the level check"),
    (SHOTS / "17-academy-plan.jpg", 4.6, "left", "YOUR WEEK", "Daily minutes. Clear milestones."),
    (STILLS / "nvs-chapter-practice.jpg", 3.4, "in", "", ""),
    (SHOTS / "04-dashboard-studio.jpg", 5.2, "in", "03  PRACTICE STUDIO", "Warm cards. Pick a set. Start."),
    (SHOTS / "05-dashboard-classic.jpg", 4.4, "right", "CLASSIC THEME", "Navy academy layout — same catalog"),
    (SHOTS / "06-practice-task-types.jpg", 4.6, "in", "TASK DECKS", "Read Aloud, Essay, and every skill"),
    (SHOTS / "15-practice-question.jpg", 5.2, "in", "LIVE QUESTION", "Timer, mic, score — after login"),
    (STILLS / "nvs-chapter-study.jpg", 3.4, "in", "", ""),
    (SHOTS / "25-study-desk.jpg", 5.8, "in", "04  STUDY DESK", "Scoring, 14-day plans, templates, traps"),
    (STILLS / "nvs-chapter-speak.jpg", 3.4, "in", "", ""),
    (SHOTS / "26-speaking.jpg", 6.0, "in", "05  ONE-TO-ONE", "A free examiner in the browser. 250+ questions."),
    (STILLS / "nvs-chapter-mocks.jpg", 3.4, "in", "", ""),
    (SHOTS / "08-mock-tests.jpg", 4.6, "right", "06  MOCK LIBRARY", "Full and section papers per exam"),
    (SHOTS / "09-mock-ielts.jpg", 4.6, "in", "IELTS MOCK", "Paper-style reading. Timed."),
    (SHOTS / "10-mock-pte.jpg", 4.6, "left", "PTE FULL MOCK", "Device check, then the real flow"),
    (SHOTS / "11-english-basics.jpg", 4.4, "in", "07  ENGLISH BASICS", "Grammar path. Eight questions a topic."),
    (SHOTS / "13-certificates.jpg", 4.4, "right", "08  CERTIFICATES", "Pathway, module, and mock credentials"),
    (SHOTS / "19-admin-overview.jpg", 5.0, "in", "09  ADMIN CMS", "Add exams, tasks, and questions yourself"),
    (STILLS / "nvs-end-card.jpg", 7.0, "in", "100% FREE", "exam-center-w40t.onrender.com"),
]


def run(cmd: list[str]) -> None:
    pretty = " ".join(cmd[:10])
    print("+", pretty, "...")
    subprocess.run(cmd, check=True)


def ken_burns(kind: str, frames: int) -> str:
    d = max(frames, 1)
    if kind == "right":
        z = "min(zoom+0.00048,1.10)"
        x = f"min(iw-iw/zoom,on*(iw-iw/zoom)/{d})"
        y = "ih/2-(ih/zoom/2)"
    elif kind == "left":
        z = "min(zoom+0.00048,1.10)"
        x = f"max(0,(iw-iw/zoom)-on*(iw-iw/zoom)/{d})"
        y = "ih/2-(ih/zoom/2)"
    elif kind == "out":
        z = f"if(eq(on,0),1.12,max(1.0,1.12-0.00055*on))"
        x = "iw/2-(iw/zoom/2)"
        y = "ih/2-(ih/zoom/2)"
    else:
        z = "min(zoom+0.00062,1.12)"
        x = "iw/2-(iw/zoom/2)"
        y = "ih/2-(ih/zoom/2)"
    return f"zoompan=z='{z}':x='{x}':y='{y}':d={frames}:s=1920x1080:fps={FPS}"


def draw_tracked(draw: ImageDraw.ImageDraw, xy: tuple[float, float], text: str, font: ImageFont.FreeTypeFont, fill, tracking: float = 3.2) -> None:
    x, y = xy
    for ch in text:
        draw.text((x, y), ch, font=font, fill=fill)
        x += draw.textlength(ch, font=font) + tracking


def make_overlay(index: int, title: str, caption: str) -> Path:
    img = Image.new("RGBA", (1920, 1080), (0, 0, 0, 0))
    draw = ImageDraw.Draw(img)
    title_font = ImageFont.truetype(FONT_BOLD, 36)
    cap_font = ImageFont.truetype(FONT, 22)
    mark_font = ImageFont.truetype(FONT, 15)

    top_h, bot_h = (88, 88) if title else (56, 56)
    draw.rectangle([0, 0, 1920, top_h], fill=(8, 6, 4, 188))
    draw.rectangle([0, 1080 - bot_h, 1920, 1080], fill=(8, 6, 4, 204))
    draw.rectangle([0, top_h, 1920, top_h + 2], fill=(196, 154, 90, 150))
    draw.rectangle([0, 1080 - bot_h - 2, 1920, 1080 - bot_h], fill=(196, 154, 90, 150))

    draw_tracked(draw, (1648, 28 if title else 18), "NEXTVERSESKILL", mark_font, (212, 186, 140, 200), tracking=2.4)

    if title:
        draw_tracked(draw, (72, 1014), title.upper(), title_font, (244, 232, 216, 255), tracking=3.6)
    if caption:
        draw.text((72, 32), caption, font=cap_font, fill=(220, 204, 184, 235))

    path = BUILD / f"ovl-{index:02d}.png"
    img.save(path, "PNG")
    return path


def render_clip(index: int, image: Path, duration: float, kind: str, title: str, caption: str) -> Path:
    if not image.exists():
        raise FileNotFoundError(image)
    frames = int(round(duration * FPS))
    overlay = make_overlay(index, title, caption)
    out = BUILD / f"clip-{index:02d}.mp4"
    vf = (
        f"[0:v]scale=3840:2160:force_original_aspect_ratio=increase:flags=lanczos,"
        f"crop=3840:2160,"
        f"{ken_burns(kind, frames)},"
        f"eq=contrast=1.05:saturation=0.94:gamma=0.98[base];"
        f"[base][1:v]overlay=0:0,"
        f"format=yuv420p"
    )
    run([
        "ffmpeg", "-y", "-hide_banner", "-loglevel", "error",
        "-loop", "1", "-i", str(image),
        "-loop", "1", "-i", str(overlay),
        "-filter_complex", vf,
        "-t", f"{duration:.2f}",
        "-r", str(FPS),
        "-an",
        "-c:v", "libx264", "-preset", "veryfast", "-crf", "18",
        "-pix_fmt", "yuv420p",
        str(out),
    ])
    return out


def is_chapter(scene: tuple) -> bool:
    return "nvs-chapter" in scene[0].name or "nvs-end" in scene[0].name or "nvs-open-title" in scene[0].name


def xfade_and_mix(clips: list[Path], durations: list[float], score: Path, final: Path) -> float:
    cmd: list[str] = ["ffmpeg", "-y", "-hide_banner", "-loglevel", "error"]
    for clip in clips:
        cmd += ["-i", str(clip)]
    cmd += ["-i", str(score)]

    filters: list[str] = []
    last = "0:v"
    acc = durations[0]
    for i in range(1, len(clips)):
        offset = acc - XFADE
        label = f"v{i}"
        trans = "fadeblack" if is_chapter(SCENES[i]) or is_chapter(SCENES[i - 1]) else "fade"
        filters.append(
            f"[{last}][{i}:v]xfade=transition={trans}:duration={XFADE}:offset={offset:.3f}[{label}]"
        )
        last = label
        acc = acc + durations[i] - XFADE

    audio_in = len(clips)
    fade_out_at = max(2.0, acc - 5.4)
    filters.append(
        f"[{audio_in}:a]loudnorm=I=-16:TP=-1.5:LRA=11,"
        f"afade=t=in:st=0:d=2.6,afade=t=out:st={fade_out_at:.2f}:d=5.2[a]"
    )

    cmd += [
        "-filter_complex", ";".join(filters),
        "-map", f"[{last}]",
        "-map", "[a]",
        "-c:v", "libx264", "-preset", "medium", "-crf", "18",
        "-c:a", "aac", "-b:a", "192k",
        "-pix_fmt", "yuv420p",
        "-shortest",
        "-movflags", "+faststart",
        str(final),
    ]
    run(cmd)
    return acc


def main() -> int:
    BUILD.mkdir(parents=True, exist_ok=True)
    score = SHOW / "score-original.wav"
    if not score.exists():
        run([sys.executable, str(SHOW / "compose_score.py")])

    clips: list[Path] = []
    durations: list[float] = []
    for i, scene in enumerate(SCENES, start=1):
        image, duration, kind, title, caption = scene
        clips.append(render_clip(i, image, duration, kind, title, caption))
        durations.append(duration)
        print(f"clip {i:02d}  {duration:4.1f}s  {image.name}")

    final = SHOW / "nextverseskill-showcase.mp4"
    total = xfade_and_mix(clips, durations, score, final)
    print(f"\nShowcase ready: {final}")
    print(f"Duration ~{total:.1f}s  size={final.stat().st_size / 1_000_000:.1f} MB")
    return 0


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except subprocess.CalledProcessError as err:
        print("ffmpeg failed", err, file=sys.stderr)
        raise
