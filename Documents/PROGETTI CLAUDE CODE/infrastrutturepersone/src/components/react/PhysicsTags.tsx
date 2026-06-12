import { useEffect, useRef } from "react";
import Matter from "matter-js";

export interface PhysicsTag {
  label: string;
  bg: string;
  color: string;
}

interface Props {
  tags: PhysicsTag[];
  height?: number;
}

/** Pill che cadono con gravità nel contenitore e si possono trascinare (replica del Physics.tsx Framer). */
export default function PhysicsTags({ tags, height = 420 }: Props) {
  const containerRef = useRef<HTMLDivElement>(null);
  const initialized = useRef(false);

  useEffect(() => {
    const container = containerRef.current;
    if (!container) return;

    const io = new IntersectionObserver((entries) => {
      if (!entries[0].isIntersecting || initialized.current) return;
      initialized.current = true;
      io.disconnect();

      const bounds = container.getBoundingClientRect();
      const engine = Matter.Engine.create({ gravity: { x: 0, y: 1 } });
      const wallOpts = { isStatic: true, friction: 2 };
      const walls = [
        Matter.Bodies.rectangle(bounds.width / 2, bounds.height + 50, bounds.width + 200, 100, wallOpts),
        Matter.Bodies.rectangle(bounds.width / 2, -bounds.height, bounds.width + 200, 100, wallOpts),
        Matter.Bodies.rectangle(-50, bounds.height / 2, 100, bounds.height * 3, wallOpts),
        Matter.Bodies.rectangle(bounds.width + 50, bounds.height / 2, 100, bounds.height * 3, wallOpts),
      ];
      Matter.World.add(engine.world, walls);

      const els = Array.from(container.querySelectorAll<HTMLElement>("[data-pill]"));
      const bodies = els.map((el) => {
        const r = el.getBoundingClientRect();
        const x = Math.random() * Math.max(bounds.width - r.width, 1) + r.width / 2;
        const y = -Math.random() * 300 - r.height;
        return Matter.Bodies.rectangle(x, y, r.width, r.height, {
          friction: 0.1,
          frictionAir: 0.01,
          density: 0.001,
          restitution: 0.25,
          chamfer: { radius: r.height / 2 },
        });
      });
      Matter.World.add(engine.world, bodies);

      const mouse = Matter.Mouse.create(container);
      const mouseConstraint = Matter.MouseConstraint.create(engine, {
        mouse,
        constraint: { stiffness: 0.2, angularStiffness: 0 },
      });
      Matter.Composite.add(engine.world, mouseConstraint);
      // Lascia passare lo scroll della pagina
      mouse.element.removeEventListener("wheel", (mouse as any).mousewheel);

      let raf = 0;
      const update = () => {
        raf = requestAnimationFrame(update);
        bodies.forEach((body, i) => {
          const el = els[i];
          if (!el) return;
          el.style.visibility = "visible";
          el.style.transform = `translate(${body.position.x - el.offsetWidth / 2}px, ${body.position.y - el.offsetHeight / 2}px) rotate(${body.angle}rad)`;
        });
        Matter.Engine.update(engine);
      };
      update();

      return () => cancelAnimationFrame(raf);
    }, { threshold: 0.4 });

    io.observe(container);
    return () => io.disconnect();
  }, []);

  return (
    <div ref={containerRef} style={{ position: "relative", height, overflow: "hidden", width: "100%" }}>
      {tags.map((t) => (
        <span
          key={t.label}
          data-pill
          style={{
            position: "absolute",
            top: 0,
            left: 0,
            visibility: "hidden",
            background: t.bg,
            color: t.color,
            borderRadius: 999,
            padding: "14px 28px",
            fontSize: 16,
            fontWeight: 500,
            whiteSpace: "nowrap",
            userSelect: "none",
            cursor: "grab",
            willChange: "transform",
          }}
        >
          {t.label}
        </span>
      ))}
    </div>
  );
}
